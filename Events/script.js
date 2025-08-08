function getRandomColor() {
    const colors = ["#FFDDC1", "#FFABAB", "#FFC3A0", "#D5AAFF", "#85E3FF", "#B9FBC0", "#F3FFE3", "#F9C6C9"];
    return colors[Math.floor(Math.random() * colors.length)];
}

// Function to fetch and display events from the database
function fetchEvents() {
    fetch("fetch_events.php")
        .then(response => response.json())
        .then(data => {
            if (!Array.isArray(data)) {
                console.error("Invalid response:", data);
                return;
            }

            let eventContainer = document.getElementById("eventsContainer");
            eventContainer.innerHTML = ""; // Clear previous events

            data.forEach(event => {
                let eventCard = document.createElement("div");
                eventCard.classList.add("event-card");
                eventCard.style.backgroundColor = getRandomColor();

                eventCard.innerHTML = `
                    <img src="${event.event_image}" alt="${event.event_title}">
                    <h3>${event.event_title}</h3>
                    <p><b>Start Date:</b> ${event.start_date || "N/A"} | <b>End Date:</b> ${event.end_date || "N/A"}</p>
                    <button class="details-btn" data-title="${event.event_title}" 
                            data-start="${event.start_date}" data-end="${event.end_date}" 
                            data-description="${event.event_description}" 
                            data-pdf="${event.event_pdf}">
                        Details
                    </button>
                `;

                eventContainer.appendChild(eventCard);
            });

            // Attach event listeners to details buttons
            document.querySelectorAll(".details-btn").forEach(button => {
                button.addEventListener("click", function () {
                    openModal(this);
                });
            });
        })
        .catch(error => console.error("Error fetching events:", error));
}
document.getElementById("start_date").addEventListener("change", function () {
    let startDate = this.value;
    document.getElementById("end_date").setAttribute("min", startDate);
});

// Apply random colors on page load
document.addEventListener("DOMContentLoaded", function () {
    fetchEvents();

    // Apply background color to existing event cards
    document.querySelectorAll(".event-card").forEach(card => {
        card.style.backgroundColor = getRandomColor();
    });

    // Apply background color to form if it exists
    const form = document.querySelector(".form-container");
    if (form) {
        form.style.backgroundColor = getRandomColor();
    }
});

function openEventForm() {
    document.getElementById("eventFormModal").style.display = "block";

    // Get today's date in YYYY-MM-DD format
    let today = new Date().toISOString().split('T')[0];

    // Set min date for start and end date fields
    document.getElementById("start_date").setAttribute("min", today);
    document.getElementById("end_date").setAttribute("min", today);

    // Clear form fields
    document.getElementById("event_name").value = "";
    document.getElementById("event_image").value = "";
    document.getElementById("start_date").value = "";
    document.getElementById("end_date").value = "";
    document.getElementById("event_description").value = "";
    document.getElementById("pdf_file").value = "";
}


function closeEventForm() {
    document.getElementById("eventFormModal").style.display = "none";
}

function saveEvent(event) {
    event.preventDefault(); // Prevent the default form submission

    let formData = new FormData(document.getElementById("eventForm"));

    fetch("save_event.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === "success") {
            // alert("Event saved successfully!");
            window.location.reload(); // Reload the page
        } else {
            alert("Error saving event: " + result.message);
        }
    })
    .catch(error => console.error("Error saving event:", error));
}

// Function to open modal and show event details
function openModal(button) {
    document.getElementById("modalEventName").innerText = button.getAttribute("data-title");
    document.getElementById("modalStartDate").innerText = button.getAttribute("data-start");
    document.getElementById("modalEndDate").innerText = button.getAttribute("data-end");
    document.getElementById("modalDescription").innerText = button.getAttribute("data-description");

    let pdfElement = document.getElementById("modalPdf");
    let pdfLink = button.getAttribute("data-pdf");

    if (pdfLink && pdfLink !== "null" && pdfLink !== "") {
        pdfElement.innerHTML = `<a href="${pdfLink}" target="_blank">View PDF</a>`;
    } else {
        pdfElement.innerHTML = "No PDF uploaded.";
    }

    document.getElementById("eventModal").style.display = "block";
}

function closeModal() {
    document.getElementById("eventModal").style.display = "none";
}
