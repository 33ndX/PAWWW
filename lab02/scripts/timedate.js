function getTheDate() {
    toDays = new Date()
    theDate = '' + (toDays.getMonth() + 1) + " / " + toDays.getDate() + " / " + (toDays.getYear() - 100)
    document.getElementById("data").innerHTML = theDate
}

var timerID = null
var timerRunning = false

function stopClock() {
    if (timerRunning) {
        clearTimeout(timerID)
    }
    timerRunning = false
}

function startClock() {
    stopClock()
    getTheDate()
    showTime()
}

function showTime() {
    var now = new Date()
    var hours = now.getHours()
    var minutes = now.getMinutes()
    var seconds = now.getSeconds()
    var timeValue = '' + ((hours > 12) ? hours - 12 : hours)
    timeValue += ((minutes < 10) ? ':0' : ':') + minutes
    timeValue += ((seconds < 10) ? ':0' : ':') + seconds
    timeValue += (hours >= 12) ? ' P.M.' : ' A.M.'
    document.getElementById('zegarek').innerHTML = timeValue
    timerID = setTimeout('showTime()', 1000)
    timerRunning = true
    showDayOfWeek()
}

function showDayOfWeek() {
    var days = ['Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota', "Niedziela"];
    var today = new Date();
    var dayName = days[today.getDay() - 1];
    var dayOfWeek = document.getElementById('dzienTygodnia');
    dayOfWeek.innerHTML = dayName;
}