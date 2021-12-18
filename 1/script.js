const currency = [ 'USD', 'EUR', 'SEK', 'JPY', 'CAD']


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

function updateCurrency() {
    $.ajax({
        url:"https://www.cbr-xml-daily.ru/daily_json.js",
        dataType: 'json',
        success: function(response) {
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

$("#update").click((event) => {
    updateTemperature()
    updateCurrency()
    alert("Обновлено")
})

window.onload = () => {
    updateTemperature()
    updateCurrency()
}