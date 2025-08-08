// This script can be included in the homepage to automatically refresh announcements
document.addEventListener("DOMContentLoaded", () => {
    // Initial refresh after page load
    setTimeout(refreshAnnouncements, 5000)
  
    // Set up periodic refresh
    setInterval(refreshAnnouncements, 300000) // Refresh every 5 minutes
  
    function refreshAnnouncements() {
      fetch("fetch_announcements.php", {
        method: "GET",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      })
        .then((response) => response.json())
        .then((data) => {
          const tickerElement = document.getElementById("announcements-ticker")
          let tickerHtml = ""
  
          if (data.length > 0) {
            data.forEach((announcement) => {
              tickerHtml += `<div class="announcement-item">
                          <span class="logo-placeholder">NEW</span>
                          ${announcement.title}
                      </div>`
            })
          } else {
            tickerHtml += `<div class="announcement-item">
                      <span class="logo-placeholder">INFO</span>
                      No announcements available at this time
                  </div>`
          }
  
          tickerElement.innerHTML = tickerHtml
  
          // Reset animation
          tickerElement.style.animation = "none"
          setTimeout(() => {
            tickerElement.style.animation = "ticker-scroll 30s linear infinite"
          }, 10)
        })
        .catch((error) => {
          console.error("Error refreshing announcements:", error)
        })
    }
  })
  
  