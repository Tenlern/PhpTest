<?php

namespace Life;

class Game
{
    /**
     * @var array Словарь настроек симуляции
     */
    private mixed $opts = [];

    /**
     * @var int Метка времени начала симуляции
     */
    private int $start_time = 0;

    /**
     * @var int Счетчик кадров
     */
    private int $frame_count = 0;

    /**
     * @var array Массив хешей поколений
     */
    private array $generation_hashes = [];

    /**
     * @var Grid Отображение сетки в терминале
     */
    private Grid $grid;


    /**
     * Создает новый экземпляр симуляции
     * @param array $opts Набор параметров
     */
    public function __construct(array $opts)
    {
        // Настройки по умолчанию
        $defaults = [
            'timeout' => 5000,
            'rand_max' => 5,
            'realtime' => TRUE,
            'max_frame_count' => 0,
            'template' => NULL,
            'keep_alive' => 0,
            'random' => TRUE,
            'width' => 64,
            'height' => 64,
            'cell' => 'O',
            'empty' => ' ',
        ];
        $this->opts = $defaults;

        if (isset($this->$opts['template']) && !isset($this->$opts['random'])) {
            // Disable random when template is set.
            $this->$opts['random'] = FALSE;
        }
        $this->start_time = time();
        $this->grid = new Grid($this->opts['width'], $this->opts['height']);
        $this->grid->generateCells($this->opts['random'], $this->opts['rand_max']);
    }

    /**
     * Цикл отображения симуляции
     * Сначала рендер нового кадра, после чего следуют проверки не превышен ли лимит кадров
     * и не пришел ли процесс к бесконечному циклу
     */
    public function loop(): void {
        while (TRUE) {
            $this->frame_count++;
            if ($this->opts['realtime']) {
                $this->render();
                $this->renderFooter();
                usleep($this->opts['timeout']);
                $this->clear();
            }
            $this->newGeneration();
            if ($this->opts['max_frame_count'] && $this->frame_count >= $this->opts['max_frame_count']) {
                break;
            }
            if (!$this->opts['keep_alive'] && $this->isEndlessLoop()) {
                break;
            }
        }

        if (!$this->opts['realtime']) {
            // Draw the last frame.
            $this->clear();
            $this->render();
        }
    }

    /**
     * Обработка создания нового поколения для всех клеток.
     *
     * Правила:
     * 1. «Изоляция» - Активная клетка, у которой активных соседей один или меньше, «умрет» в следующем состоянии поля.
     * 2. «Перегрузка» - Активная клетка, у которой активных соседей четыре или больше, «умрет» в следующем состоянии поля.
     * 3. «Вымирание» - Активная клетка останется такой, только если у неё ровно 2 или 3 активных соседа,
     * иначе «умрет» в следующем состоянии поля.
     * 4. «Активация» - Мертвая клетка, у которой ровно три активных соседа,
     * становится активной в следующем состоянии поля.
     */
    private function newGeneration(): void {
        $cells = &$this->grid->cells;
        $kill_queue = $born_queue = [];

        for ($y = 0; $y < $this->grid->getHeight(); $y++) {
            for ($x = 0; $x < $this->grid->getWidth(); $x++) {

                // All cell activity is determined by the neighbor count.
                $neighbor_count = $this->getAliveNeighborCount($x, $y);

                if ($cells[$y][$x] && ($neighbor_count < 2 || $neighbor_count > 3)) {
                    $kill_queue[] = [$y, $x];
                }
                if (!$cells[$y][$x] && $neighbor_count === 3) {
                    $born_queue[] = [$y, $x];
                }
            }
        }

        foreach ($kill_queue as $c) {
            $cells[$c[0]][$c[1]] = 0;
        }

        foreach ($born_queue as $c) {
            $cells[$c[0]][$c[1]] = 1;
        }

        if (!$this->opts['keep_alive']) {
            $this->trackGeneration();
        }
    }

    /**
     * Является ли цикл бесконечным на основании хешей прошлых поколений
     * @return bool
     */
    private function isEndlessLoop(): bool {
        foreach ($this->generation_hashes as $hash) {
            $found = -1;
            foreach ($this->generation_hashes as $hash2) {
                if ($hash === $hash2) {
                    $found++;
                }
            }
            if ($found >= 3) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Отслеживает популяцию, чтобы прервать цикл при полном вымирании.
     */
    private function trackGeneration(): void {
        static $pointer;

        if (!isset($pointer)) {
            $pointer = 0;
        }

        $hash = md5(json_encode($this->grid->cells));
        $this->generation_hashes[$pointer] = $hash;
        $pointer++;

        if ($pointer > 20) {
            $pointer = 0;
        }
    }

    /**
     * Получаем количество живых соседей клетки.
     *
     * @param int $x Столбец клетки
     * @param int $y Строка клетки
     *
     * @return int Кол-во
     */
    private function getAliveNeighborCount(int $x,int $y): int {
        $alive_count = 0;
        for ($y2 = $y - 1; $y2 <= $y + 1; $y2++) {
            if ($y2 < 0 || $y2 >= $this->grid->getHeight()) {
                // Out of range.
                continue;
            }
            for ($x2 = $x - 1; $x2 <= $x + 1; $x2++) {
                if ($x2 == $x && $y2 == $y) {
                    // Current cell spot.
                    continue;
                }
                if ($x2 < 0 || $x2 >= $this->grid->getWidth()) {
                    // Out of range.
                    continue;
                }
                if ($this->grid->cells[$y2][$x2]) {
                    $alive_count += 1;
                }
            }
        }
        return $alive_count;
    }

    /**
     * Перенос курсора в начало сетки для рендера нового кадра.
     */
    private function clear(): void {
        echo "\033[0;0H";
    }

    /**
     * Рендер сетки в окне терминала.
     */
    private function render(): void {
        foreach ($this->grid->cells as $y => $row) {
            $print_row = '';
            foreach ($row as $x => $cell) {
                $print_row .= ($cell ? $this->opts['cell'] : $this->opts['empty']);
            }
            print $print_row . "\n";
        }
    }

    /**
     * Рендер футера с отчетом о состоянии симуляции.
     */
    private function renderFooter(): void {
        print str_repeat('_', $this->opts['width']) . "\n";
        // Return to the beginning of the line
        echo "\r";
        // Erase to the end of the line
        echo "\033[K";
        print $this->getStatus() . "\n";
    }

    /**
     * Вывод отчета.
     *
     * @return string Содержание отчета
     */
    private function getStatus(): string {
        $live_cells = $this->grid->countLiveCells();
        $elapsed_time = time() - $this->start_time;
        if ($elapsed_time > 0) {
            $fps = number_format($this->frame_count / $elapsed_time, 1);
        }
        else {
            $fps = 'Calculating...';
        }
        return " Gen: {$this->frame_count} | Cells: $live_cells | Elapsed Time: {$elapsed_time}s | FPS: {$fps}";
    }
}
