document.addEventListener("DOMContentLoaded", () => {
  // DOM Elements
  const meetingDateInput = document.getElementById("meeting-date")
  if (meetingDateInput) {
    const today = new Date().toISOString().split("T")[0] // Get today's date in YYYY-MM-DD format
    meetingDateInput.setAttribute("min", today)
  }
  const newMeetingBtn = document.getElementById("new-meeting-btn")
  const meetingForm = document.getElementById("meeting-form")
  const minutesList = document.getElementById("minutes-list")
  const meetingDetails = document.getElementById("meeting-details")
  const minutesForm = document.getElementById("minutes-form")
  const cancelBtn = document.getElementById("cancel-btn")
  const searchBtn = document.getElementById("search-btn")
  const searchInput = document.getElementById("search-input")
  const sortSelect = document.getElementById("sort-select")
  const minutesContainer = document.getElementById("minutes-container")
  const editBtn = document.getElementById("edit-btn")
  const deleteBtn = document.getElementById("delete-btn")
  const backBtn = document.getElementById("back-btn")
  const confirmationModal = document.getElementById("confirmation-modal")
  const confirmDelete = document.getElementById("confirm-delete")
  const cancelDelete = document.getElementById("cancel-delete")
  const formTitle = document.getElementById("form-title")
  const gridViewBtn = document.getElementById("grid-view-btn")
  const listViewBtn = document.getElementById("list-view-btn")
  const attendeeInput = document.getElementById("attendee-input")
  const attendeesTags = document.getElementById("attendees-tags")
  const attendeesHidden = document.getElementById("attendees")
  const actionItemsContainer = document.getElementById("action-items-container")
  const actionItemsHidden = document.getElementById("action-items")

  // Initialize delete functionality
  if (deleteBtn) {
    deleteBtn.addEventListener("click", function(e) {
      e.preventDefault();
      e.stopPropagation();
      console.log("Delete button clicked, currentMeetingId:", currentMeetingId);
      showDeleteConfirmation();
    });
  }
  if (confirmDelete) {
    confirmDelete.addEventListener("click", function(e) {
      e.preventDefault();
      e.stopPropagation();
      console.log("Confirm delete clicked");
      deleteMeetingMinutes();
    });
  }
  if (cancelDelete) {
    cancelDelete.addEventListener("click", function(e) {
      e.preventDefault();
      e.stopPropagation();
      hideDeleteConfirmation();
    });
  }

  // Add click event listener to close modal when clicking outside
  window.addEventListener("click", function(event) {
    if (event.target === confirmationModal) {
      hideDeleteConfirmation();
    }
  });

  // Current meeting ID for edit/delete operations
  let currentMeetingId = null
  let attendeesList = []
  let actionItemsList = []

  // Add this at the beginning of the file
  let isAdmin = true;

  // Check if user is logged in
  checkUserSession()

  // Load meeting minutes
  loadMeetingMinutes()

  // Event Listeners
  if (newMeetingBtn) {
    newMeetingBtn.addEventListener("click", function(e) {
      e.preventDefault();
      $('#newMeetingModal').modal('show');
    });
  }
  cancelBtn.addEventListener("click", hideForm)
  minutesForm.addEventListener("submit", validateAndSaveMinutes)
  searchBtn.addEventListener("click", searchMinutes)
  searchInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter") {
      searchMinutes()
    }
  })
  sortSelect.addEventListener("change", sortMinutes)
  editBtn.addEventListener("click", editMeetingMinutes)
  backBtn.addEventListener("click", showMinutesList)

  // Add event listeners for collapsible sections
  document.querySelectorAll(".section-header").forEach((header) => {
    header.addEventListener("click", toggleSection)
  })

  // Attendees input handling
  attendeeInput.addEventListener("keydown", function (e) {
    if (e.key === "Enter" && this.value.trim() !== "") {
      e.preventDefault()
      addAttendee(this.value.trim())
      this.value = ""
    }
  })

  // Action items handling
  document.addEventListener("click", (e) => {
    if (
      e.target.classList.contains("add-action-item") ||
      e.target.parentElement.classList.contains("add-action-item")
    ) {
      addActionItemRow()
    }

    if (
      e.target.classList.contains("remove-action-item") ||
      e.target.parentElement.classList.contains("remove-action-item")
    ) {
      const row = e.target.closest(".action-item-row")
      if (row && actionItemsContainer.children.length > 1) {
        row.remove()
      }
    }

    if (e.target.classList.contains("remove-tag") || e.target.parentElement.classList.contains("remove-tag")) {
      const tag = e.target.closest(".tag")
      if (tag) {
        const attendeeName = tag.getAttribute("data-name")
        removeAttendee(attendeeName)
      }
    }
  })

  // Form validation
  document.querySelectorAll("#minutes-form input[required], #minutes-form textarea[required]").forEach((field) => {
    field.addEventListener("blur", function () {
      validateField(this)
    })

    field.addEventListener("input", function () {
      clearValidation(this)
    })
  })

  // Save meeting functionality
  const saveMeetingBtn = document.getElementById("save-meeting");
  if (saveMeetingBtn) {
    saveMeetingBtn.addEventListener("click", function(e) {
      e.preventDefault();
      const form = document.getElementById("new-meeting-form");
      const formData = new FormData(form);

      fetch('save_meeting.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast("Meeting created successfully", "success");
          $('#newMeetingModal').modal('hide');
          loadMeetingMinutes(); // Refresh the meetings list
          form.reset();
        } else {
          showToast(data.error || "Error creating meeting", "error");
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast("Error creating meeting", "error");
      });
    });
  }

  // Functions
  function checkUserSession() {
    fetch("check_session.php")
      .then((response) => response.json())
      .then((data) => {
        if (!data.loggedIn) {
          window.location.href = "../login.php"
        } else {
          document.getElementById("user").textContent = data.user_name
          isAdmin = data.is_admin || false;
          updateAdminControls();
        }
      })
      .catch((error) => {
        console.error("Error checking session:", error)
        showToast("Error checking user session", "error")
      })
  }

  function updateAdminControls() {
    const adminControls = document.querySelectorAll('.admin-only');
    const newMeetingBtn = document.getElementById('new-meeting-btn');
    
    if (isAdmin) {
        adminControls.forEach(control => {
            control.style.display = 'inline-block';
        });
        if (newMeetingBtn) {
            newMeetingBtn.style.display = 'inline-block';
        }
    } else {
        adminControls.forEach(control => {
            control.style.display = 'none';
        });
        if (newMeetingBtn) {
            newMeetingBtn.style.display = 'none';
        }
    }
  }

  function loadMeetingMinutes(searchTerm = '') {
    console.log('loadMeetingMinutes called');
    const minutesContainer = document.getElementById('minutes-container');
    if (!minutesContainer) {
        console.error('minutesContainer not found');
        return;
    }
    minutesContainer.innerHTML = '<div class="loading">Loading meeting minutes...</div>';

    fetch('../Meeting/get_minutes.php')
        .then(response => response.json())
        .then(response => {
            if (!response.success) {
                throw new Error(response.error || 'Error loading meetings');
            }
            
            const data = response.data;
            if (data.length === 0) {
                minutesContainer.innerHTML = '<div class="no-data">No meeting minutes found.</div>';
                return;
            }

            minutesContainer.innerHTML = '';
            data.forEach(meeting => {
                const minuteCard = document.createElement('div');
                minuteCard.className = 'minute-card';
                minuteCard.dataset.id = meeting.id;

                minuteCard.innerHTML = `
                    <div class="minute-header">
                        <h3>${meeting.title}</h3>
                        <div class="minute-actions">
                            <button class="view-btn" onclick="viewMeetingDetails(${meeting.id})">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="delete-btn" onclick="deleteMeeting(${meeting.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                    <div class="minute-info">
                        <p><i class="fas fa-calendar"></i> ${meeting.formatted_date}</p>
                        ${meeting.formatted_time ? `<p><i class="fas fa-clock"></i> ${meeting.formatted_time}</p>` : ''}
                    </div>
                    <div class="minute-preview">
                        <p>${meeting.agenda}</p>
                    </div>
                `;

                minutesContainer.appendChild(minuteCard);
            });
        })
        .catch(error => {
            console.error('Error loading meetings:', error);
            minutesContainer.innerHTML = '<div class="error">Error loading meeting minutes. Please try again.</div>';
        });
  }

  function viewMeetingDetails(id) {
    console.log("Viewing meeting details for ID:", id);
    currentMeetingId = id;
    console.log("Current meeting ID set to:", currentMeetingId);

    fetch(`get_minute_details.php?id=${id}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.error) {
                showToast(data.error, "error");
                return;
            }

            // Update the UI with meeting details
            document.getElementById("detail-title").textContent = data.title;
            document.getElementById("detail-date").textContent = formatDate(data.meeting_date);
            document.getElementById("detail-time").textContent = data.meeting_time || "Not specified";

            // Parse and display attendees
            const attendeesDiv = document.getElementById("detail-attendees");
            attendeesDiv.innerHTML = "";
            try {
                const attendees = JSON.parse(data.attendees);
          attendees.forEach((attendee) => {
                    const tag = document.createElement("div");
                    tag.className = "attendee-tag";
                    tag.textContent = attendee;
                    attendeesDiv.appendChild(tag);
                });
        } catch (e) {
                console.error("Error parsing attendees:", e);
                const attendeesList = data.attendees.split(",").map(a => a.trim()).filter(a => a);
          attendeesList.forEach((attendee) => {
                    const tag = document.createElement("div");
                    tag.className = "attendee-tag";
                    tag.textContent = attendee;
                    attendeesDiv.appendChild(tag);
                });
            }

            document.getElementById("detail-agenda").textContent = data.agenda;
            document.getElementById("detail-discussion").textContent = data.discussion;

            // Parse and display action items
            const actionItemsDiv = document.getElementById("detail-action-items");
            actionItemsDiv.innerHTML = "";
            try {
                const actionItems = JSON.parse(data.action_items);
          actionItems.forEach((item) => {
                    const actionItem = document.createElement("div");
                    actionItem.className = "action-item";
            actionItem.innerHTML = `
                              <div class="action-item-text">${item.text}</div>
                              <div class="action-item-owner">${item.owner || "Unassigned"}</div>
                              <div class="action-item-due">${item.dueDate ? formatDate(item.dueDate) : "No due date"}</div>
                    `;
                    actionItemsDiv.appendChild(actionItem);
                });
        } catch (e) {
                console.error("Error parsing action items:", e);
                const actionItemsList = data.action_items.split("\n").filter(a => a.trim());
          actionItemsList.forEach((item) => {
                    const actionItem = document.createElement("div");
                    actionItem.className = "action-item";
            actionItem.innerHTML = `
                              <div class="action-item-text">${item}</div>
                              <div class="action-item-owner">-</div>
                              <div class="action-item-due">-</div>
                    `;
                    actionItemsDiv.appendChild(actionItem);
                });
            }

            // Show details view
            minutesList.classList.add("hidden");
            meetingDetails.classList.remove("hidden");
            meetingDetails.classList.add("fadeIn");

            // Update admin controls visibility
            updateAdminControls();
      })
      .catch((error) => {
            console.error("Error fetching meeting details:", error);
            showToast("Error loading meeting details", "error");
        });
  }

  function showNewMeetingForm() {
    resetForm()
    formTitle.textContent = "Create Meeting Minutes"

    // Reset attendees and action items
    attendeesList = []
    actionItemsList = []
    updateAttendeesDisplay()

    // Reset action items to just one empty row
    actionItemsContainer.innerHTML = ""
    addActionItemRow()

    // Show form with animation
    meetingForm.classList.remove("hidden")
    meetingForm.classList.add("fadeIn")
    minutesList.classList.add("hidden")
    meetingDetails.classList.add("hidden")
  }

  function hideForm() {
    meetingForm.classList.add("fadeOut")
    setTimeout(() => {
      meetingForm.classList.add("hidden")
      meetingForm.classList.remove("fadeOut")
      minutesList.classList.remove("hidden")
    }, 300)
  }

  function resetForm() {
    minutesForm.reset()
    document.getElementById("meeting-id").value = ""
    currentMeetingId = null

    // Clear validation messages
    document.querySelectorAll(".validation-message").forEach((msg) => {
      msg.textContent = ""
    })

    // Reset attendees
    attendeesTags.innerHTML = ""
    attendeesList = []
    attendeesHidden.value = JSON.stringify([])

    // Reset action items
    actionItemsContainer.innerHTML = ""
    addActionItemRow()
    actionItemsHidden.value = JSON.stringify([])

    // Remove any invalid classes
    document.querySelectorAll(".invalid").forEach((field) => {
      field.classList.remove("invalid")
    })
  }

  function validateAndSaveMinutes(e) {
    e.preventDefault()

    // Validate all required fields
    const requiredFields = document.querySelectorAll("#minutes-form input[required], #minutes-form textarea[required]")
    let isValid = true

    requiredFields.forEach((field) => {
      if (!validateField(field)) {
        isValid = false
      }
    })

    // Collect action items
    collectActionItems()

    // Update hidden fields
    attendeesHidden.value = JSON.stringify(attendeesList)
    actionItemsHidden.value = JSON.stringify(actionItemsList)

    if (!isValid) {
      showToast("Please fill in all required fields", "error")
      return
    }

    // Show loading indicator
    showToast("Saving meeting minutes...", "info")

    saveMeetingMinutes()
  }

  function saveMeetingMinutes() {
    const formData = new FormData()
    formData.append("title", document.getElementById("meeting-title").value)
    formData.append("meeting_date", document.getElementById("meeting-date").value)
    formData.append("meeting_time", document.getElementById("meeting-time").value)
    formData.append("attendees", attendeesHidden.value)
    formData.append("agenda", document.getElementById("agenda").value)
    formData.append("discussion", document.getElementById("discussion").value)
    formData.append("action_items", actionItemsHidden.value)

    if (currentMeetingId) {
      formData.append("id", currentMeetingId)
    }

    fetch("save_minutes.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok")
        }
        return response.json()
      })
      .then((data) => {
        if (data.success) {
          hideForm()
          loadMeetingMinutes()
          if (currentMeetingId) {
            showMinutesList()
          }
          showToast(
            currentMeetingId ? "Meeting minutes updated successfully" : "Meeting minutes created successfully",
            "success",
          )
        } else {
          showToast(data.error || "Error saving meeting minutes", "error")
        }
      })
      .catch((error) => {
        console.error("Error saving meeting minutes:", error)
        showToast("Error saving meeting minutes. Please check console for details.", "error")
      })
  }

  function editMeetingMinutes() {
    fetch(`get_minute_details.php?id=${currentMeetingId}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.error) {
          showToast(data.error, "error")
          return
        }

        document.getElementById("meeting-id").value = data.id
        document.getElementById("meeting-title").value = data.title
        document.getElementById("meeting-date").value = data.meeting_date
        document.getElementById("meeting-time").value = data.meeting_time || ""

        // Parse attendees
        try {
          attendeesList = JSON.parse(data.attendees)
        } catch (e) {
          // Fallback for old format
          attendeesList = data.attendees
            .split(",")
            .map((a) => a.trim())
            .filter((a) => a)
        }
        updateAttendeesDisplay()

        document.getElementById("agenda").value = data.agenda
        document.getElementById("discussion").value = data.discussion

        // Parse action items
        actionItemsContainer.innerHTML = ""
        try {
          actionItemsList = JSON.parse(data.action_items)
          actionItemsList.forEach((item) => {
            addActionItemRow(item.text, item.owner, item.dueDate)
          })
        } catch (e) {
          // Fallback for old format
          const items = data.action_items.split("\n").filter((a) => a.trim())
          items.forEach((item) => {
            addActionItemRow(item, "", "")
          })
        }

        // If no action items were added, add an empty row
        if (actionItemsContainer.children.length === 0) {
          addActionItemRow()
        }

        formTitle.textContent = "Edit Meeting Minutes"
        meetingDetails.classList.add("hidden")
        meetingForm.classList.remove("hidden")
        meetingForm.classList.add("fadeIn")
      })
      .catch((error) => {
        console.error("Error fetching meeting details for edit:", error)
        showToast("Error loading meeting details", "error")
      })
  }

  function showDeleteConfirmation(meetingId) {
    console.log('showDeleteConfirmation called with meetingId:', meetingId);
    if (meetingId) {
        currentMeetingId = meetingId;
    }
    const confirmationModal = document.getElementById('confirmation-modal');
    if (!confirmationModal) {
        console.error('Confirmation modal not found');
        return;
    }
    confirmationModal.style.display = "block";
    console.log('Confirmation modal shown');
  }

  function hideDeleteConfirmation() {
    if (confirmationModal) {
        document.querySelector(".modal-content").classList.add("fadeOut");
        setTimeout(() => {
            confirmationModal.style.display = "none";
            confirmationModal.classList.add("hidden");
            document.querySelector(".modal-content").classList.remove("fadeOut");
        }, 300);
    }
  }

  function deleteMeetingMinutes() {
    console.log("Deleting meeting:", currentMeetingId);
    if (!currentMeetingId) {
        showToast("No meeting selected", "error");
        hideDeleteConfirmation();
        return;
    }

    const formData = new FormData();
    formData.append("id", currentMeetingId);

    fetch("../Meeting/delete_meeting.php", {
        method: "POST",
        body: formData
    })
    .then(response => {
        console.log("Delete response received:", response);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log("Delete response data:", data);
        hideDeleteConfirmation();
        if (data.success) {
            showMinutesList();
            loadMeetingMinutes();
            showToast(data.message || "Meeting minutes deleted successfully", "success");
        } else {
            showToast(data.error || "Error deleting meeting minutes", "error");
        }
    })
    .catch(error => {
        console.error("Delete error:", error);
        hideDeleteConfirmation();
        showToast("Error deleting meeting minutes", "error");
    });
  }

  function showMinutesList() {
    meetingDetails.classList.add("fadeOut")
    setTimeout(() => {
      meetingDetails.classList.add("hidden")
      meetingDetails.classList.remove("fadeOut")
      minutesList.classList.remove("hidden")
      minutesList.classList.add("fadeIn")
    }, 300)
    currentMeetingId = null
  }

  function searchMinutes() {
    const searchTerm = searchInput.value.trim()
    loadMeetingMinutes(searchTerm)
  }

  function sortMinutes() {
    const searchTerm = searchInput.value.trim()
    loadMeetingMinutes(searchTerm)
  }

  function sortData(data, sortValue) {
    switch (sortValue) {
      case "date-desc":
        data.sort((a, b) => new Date(b.meeting_date) - new Date(a.meeting_date))
        break
      case "date-asc":
        data.sort((a, b) => new Date(a.meeting_date) - new Date(b.meeting_date))
        break
      case "title-asc":
        data.sort((a, b) => a.title.localeCompare(b.title))
        break
      case "title-desc":
        data.sort((a, b) => b.title.localeCompare(a.title))
        break
    }
  }

  function switchToGridView() {
    gridViewBtn.classList.add("active")
    listViewBtn.classList.remove("active")
    minutesContainer.classList.add("grid-view")
    minutesContainer.classList.remove("list-view")
  }

  function switchToListView() {
    listViewBtn.classList.add("active")
    gridViewBtn.classList.remove("active")
    minutesContainer.classList.add("list-view")
    minutesContainer.classList.remove("grid-view")
  }

  function toggleSection() {
    const targetId = this.getAttribute("data-target")
    const targetSection = document.getElementById(targetId)
    const icon = this.querySelector("i.fas.fa-chevron-down")

    if (targetSection.style.display === "none" || !targetSection.style.display) {
      targetSection.style.display = "block"
      icon.classList.add("fa-rotate-180")
    } else {
      targetSection.style.display = "none"
      icon.classList.remove("fa-rotate-180")
    }
  }

  function addAttendee(name) {
    if (name && !attendeesList.includes(name)) {
      attendeesList.push(name)
      updateAttendeesDisplay()
    }
  }

  function removeAttendee(name) {
    const index = attendeesList.indexOf(name)
    if (index !== -1) {
      attendeesList.splice(index, 1)
      updateAttendeesDisplay()
    }
  }

  function updateAttendeesDisplay() {
    attendeesTags.innerHTML = ""
    attendeesList.forEach((name) => {
      const tag = document.createElement("div")
      tag.className = "tag"
      tag.setAttribute("data-name", name)
      tag.innerHTML = `
                  ${name}
                  <span class="remove-tag"><i class="fas fa-times"></i></span>
              `
      attendeesTags.appendChild(tag)
    })

    // Update hidden field
    attendeesHidden.value = JSON.stringify(attendeesList)
  }

  function addActionItemRow(text = "", owner = "", dueDate = "") {
    const row = document.createElement("div")
    row.className = "action-item-row"

    // Determine if this is the first row being added
    const isFirstRow = actionItemsContainer.children.length === 0

    row.innerHTML = `
              <input type="text" class="action-item-input" placeholder="Action item" value="${text}">
              <input type="text" class="action-owner-input" placeholder="Owner" value="${owner}">
              <input type="date" class="action-due-input" value="${dueDate}">
              <button type="button" class="btn-icon ${isFirstRow ? "add-action-item" : "remove-action-item"}">
                  <i class="fas fa-${isFirstRow ? "plus" : "minus"}"></i>
              </button>
          `

    actionItemsContainer.appendChild(row)
  }

  function collectActionItems() {
    actionItemsList = []
    document.querySelectorAll(".action-item-row").forEach((row) => {
      const text = row.querySelector(".action-item-input").value.trim()
      const owner = row.querySelector(".action-owner-input").value.trim()
      const dueDate = row.querySelector(".action-due-input").value

      if (text) {
        actionItemsList.push({
          text: text,
          owner: owner,
          dueDate: dueDate,
        })
      }
    })

    actionItemsHidden.value = JSON.stringify(actionItemsList)
  }

  function validateField(field) {
    const id = field.id
    const value = field.value.trim()
    const validationMessage = document.getElementById(`${id}-validation`)

    if (!validationMessage) {
      console.error(`Validation message element not found for field: ${id}`)
      return true // Skip validation if element not found
    }

    if (!value && field.hasAttribute("required")) {
      validationMessage.textContent = "This field is required"
      field.classList.add("invalid")
      return false
    }

    validationMessage.textContent = ""
    field.classList.remove("invalid")
    return true
  }

  function clearValidation(field) {
    const id = field.id
    const validationMessage = document.getElementById(`${id}-validation`)
    validationMessage.textContent = ""
    field.classList.remove("invalid")
  }

  function formatDate(dateString) {
    if (!dateString) return "Not specified"
    const options = { year: "numeric", month: "long", day: "numeric" }
    return new Date(dateString).toLocaleDateString(undefined, options)
  }

  function showToast(message, type = "info") {
    const toast = document.createElement("div")
    toast.className = `toast ${type}`

    let icon = "info-circle"
    if (type === "success") icon = "check-circle"
    if (type === "error") icon = "exclamation-circle"

    toast.innerHTML = `
              <i class="fas fa-${icon}"></i>
              <span>${message}</span>
          `

    document.getElementById("toast-container").appendChild(toast)

    setTimeout(() => {
      toast.classList.add("slideOut")
      setTimeout(() => {
        toast.remove()
      }, 300)
    }, 3000)
  }

  // Add this new function for direct delete handling
  function deleteMeeting(meetingId) {
    console.log('Delete meeting function called with ID:', meetingId);
    
    // Check if meetingId is valid
    if (!meetingId) {
        console.error('Invalid meeting ID');
        showToast('Invalid meeting ID', 'error');
        return;
    }

    // Show confirmation dialog
    if (confirm('Are you sure you want to delete this meeting? This action cannot be undone.')) {
        console.log('User confirmed deletion');
        
        // Create form data
        const formData = new FormData();
        formData.append('id', meetingId);
        console.log('Form data created:', Object.fromEntries(formData));

        // Make the delete request
        console.log('Sending delete request to ../Meeting/delete_meeting.php');
        fetch('../Meeting/delete_meeting.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response received:', response);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                showToast('Meeting deleted successfully', 'success');
                loadMeetingMinutes(); // Reload the list
            } else {
                console.error('Delete failed:', data.error);
                showToast(data.error || 'Error deleting meeting', 'error');
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            showToast('Error deleting meeting: ' + error.message, 'error');
        });
    } else {
        console.log('User cancelled deletion');
    }
  }
})
