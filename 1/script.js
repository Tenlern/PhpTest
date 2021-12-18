const currency = [ 'USD', 'EUR', 'SEK', 'JPY', 'CAD'] // Ключи выбранных валют

/*
* Обновление сводки по температуре в Москве
* Выводит данные в подготовленный заранее блок
* Использует WeatherAPI
*/
function updateTemperature() {
    $.ajax({
        url:"http://api.weatherapi.com/v1/current.json",
        dataType: 'json',
        data: {
            'key': 'd24eafdf516b4b49be0132841211812',
            'q': 'Moscow',
            'lang': 'Ru'
        },
        success: function(response) {
            // Используем класс Date для перевода в нужный нам формат
            date = new Date(response.location.localtime)
            $("#js-date").text(`${date.getDate()}.${date.getMonth()+1}`)
            $("#js-temperature").text(response.current.temp_c)
            $("#js-feelslike-temperature").text(response.current.feelslike_c)
            $("#js-description").text(response.current.condition.text)
        },
        error: function (response) {
            console.log(response)
        }
    })
}
/*
* Обновление курса обмена для пяти валют
* Отправляет запрос к api Центробанка и заполняет блок по шаблону
*/
function updateCurrency() {
    $.ajax({
        url:"https://www.cbr-xml-daily.ru/daily_json.js",
        dataType: 'json',
        success: function(response) {
            // Очищаем блок перед работой
            $('#js-currency').html('')
            currency.forEach((element) => {
                data = response.Valute[element]
                $('#js-currency').append(
                    `<div class="bubble"><div class="container"><p>1 ${element} = ${data.Value} RUB</p>${data.Name}</div></div>`)
            })
        },
        error: function (response) {
            console.log(response)
        }
    })
}

// Привязываем функции к событиям клика по кнопке и загрузки страницы
$("#js-update").click((event) => {
    updateTemperature()
    updateCurrency()
    alert("Обновлено")
})

window.onload = () => {
    updateTemperature()
    updateCurrency()
}