var computed = false
var decimal = 0

function convert(entryform, from, to) {
    convertform = from.selectedIndex
    convertto = to.selectedIndex
    entryform.display.value = (entryform.input.value * from[convertform].value / to[convertto].value)
}

function addChar(input, character) {
    if ((character == '.' && decimal == '0') || character != '.') {
        (input.value == '' || input.value == '0') ? input.value = character : input.value += character
        convert(input.form, input.form.measure1, input.form.measure2)
        computed = true
        if (character == '.') {
            decimal = 1
        }
    }
}

function openvothcom() {
    window.open('', 'Display window', 'toolbar=np, directories=no, menubar=no')
}

function clear(form) {
    form.input.value = 0
    form.display.value = 0
    decimal = 0
}

function changeBackground(hexNumber) {
    document.body.style.backgroundColor = hexNumber
}

/* Funkcja generująca losowy kolor w palecie strony */
function getRandomColor() {
    // Paleta kolorów dopasowana do stylistyki strony
    var colors = [
        '#2c3e50',  // Ciemny niebieski (navbar, nagłówki)
        '#3498db',  // Niebieski (akcent, linki)
        '#34495e',  // Ciemniejszy szary-niebieski (hover)
        '#ecf0f1',  // Jasny szary (tło info)
        '#95a5a6',  // Szary (srebrny medal)
        '#f39c12',  // Pomarańczowy/złoty (złoty medal)
        '#2980b9',  // Ciemniejszy niebieski (hover button)
        '#e8f4f8',  // Bardzo jasny niebieski
        '#d5e8f0',  // Pastelowy niebieski
        '#bdc3c7'   // Jasny szary
    ]
    return colors[Math.floor(Math.random() * colors.length)]
}