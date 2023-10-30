function docReady(fn) {
    // see if DOM is already available
    if (document.readyState === "complete" || document.readyState === "interactive") {
        // call on next available tick
        setTimeout(fn, 1);
    } else {
        document.addEventListener("DOMContentLoaded", fn);
    }
}

// $(document).ready(() => {
docReady(function () {
    console.log("INITIALIZE");






    EmbeddableWidget.mount({
        baseUrl: "/wp-json/wpr/v1/chat",
    });
})
// })