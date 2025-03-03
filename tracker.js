(function() {
    let screenWidth = window.innerWidth || screen.width;
    let screenHeight = window.innerHeight || screen.height;
    
    let data = {
        url: window.location.href,
        referrer: document.referrer,
        userAgent: navigator.userAgent,
        language: navigator.language,
        screenResolution: `${screenWidth}x${screenHeight}`,
        timestamp: new Date().toISOString(),
    };

    fetch("https://domybustehrad.cz/deight-statistics/tracker.php?w=" + screenWidth + "&h=" + screenHeight + "&js_referer=" + encodeURIComponent(document.referrer), {
        method: "GET"
    }).then(response => console.log("✅ Návštěva zaznamenána: " + data.referrer))
    .catch(error => console.error("❌ Chyba při odesílání návštěvy:", error));

    if (document.querySelector("[data-track]")) {
        document.body.addEventListener("click", function(event) {
            let target = event.target.closest("[data-track]");
            if (!target) return;

            let eventData = {
                element: target.dataset.track,
                page: window.location.href,
                referrer: document.referrer || "Přímá návštěva",
                utm_source: new URLSearchParams(window.location.search).get("utm_source") || "",
                utm_medium: new URLSearchParams(window.location.search).get("utm_medium") || "",
                utm_campaign: new URLSearchParams(window.location.search).get("utm_campaign") || "",
                timestamp: new Date().toISOString()
            };

            fetch("https://domybustehrad.cz/deight-statistics/tracker.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(eventData)
            }).then(response => response.json())
            .then(data => console.log("✅ Událost zaznamenána:", data))
            .catch(error => console.error("❌ Chyba při odesílání události:", error));
        });
    }
})();
