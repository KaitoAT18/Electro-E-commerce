$(document).ready(function () {
    // Set the date we're counting down to (7 days from now)
    const countdownDate = new Date();
    countdownDate.setDate(countdownDate.getDate() + 7);

    // Update the countdown every 1 second
    const countdownTimer = setInterval(function () {
        // Get current date and time
        const now = new Date().getTime();

        // Find the distance between now and the countdown date
        const distance = countdownDate.getTime() - now;

        // Time calculations for days, hours, minutes and seconds
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor(
            (distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
        );
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        // Display the result
        $("#countdown-days").text(formatTime(days));
        $("#countdown-hours").text(formatTime(hours));
        $("#countdown-minutes").text(formatTime(minutes));
        $("#countdown-seconds").text(formatTime(seconds));

        // If the countdown is finished, write some text
        if (distance < 0) {
            clearInterval(countdownTimer);
            $(".hot-deal-countdown li div h3").text("00");
        }
    }, 1000);

    // Format time to always have 2 digits
    function formatTime(time) {
        return (time < 10 ? "0" : "") + time;
    }
});
