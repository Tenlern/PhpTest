<?php


namespace Life;


class Grid {

    private int $width;

    private int $height;

    public array $cells = [];

    /**
     * Конструктор новой сетки
     *
     * @param int $width
     * @param int $height
     */
    public function __construct(int $width, int $height) {
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Заполняем сетку клетками.
     *
     * @param bool $randomize Если истинно, то вместо пустого поля случайные клетки оживут
     * @param int $rand_max Максимальное количество живых клеток на старте
     * @return $this
     */
    public function generateCells(bool $randomize, int $rand_max = 10): self {
        for ($x = 0; $x < $this->width; $x++) {
            for ($y = 0; $y < $this->height; $y++) {
                if ($randomize) {
                    $this->cells[$y][$x] = $this->getRandomState($rand_max);
                }
                else {
                    $this->cells[$y][$x] = 0;
                }
            }
        }
        return $this;
    }

    /**
     * @return int Количество живых клеток
     */
    public function countLiveCells(): int {
        $count = 0;
        foreach ($this->cells as $y => $row) {
            foreach ($row as $x => $cell) {
                if ($cell) {
                    $count++;
                }
            }
        }
        return $count;
    }

    /**
     * Геттер ширины.
     * @return int Ширина сетки
     */
    public function getWidth(): int {
        return $this->width;
    }

    /**
     * Геттер высоты.
     * @return int
     */
    public function getHeight(): int {
        return $this->height;
    }

    /**
     * Получение случайного состояния клетки.
     *
     * @param int $rand_max Lower values means more "alive" cells.
     * @return bool
     */
    private function getRandomState(int $rand_max = 1): bool {
        return rand(0, $rand_max) === 0;
    }
}