document.addEventListener("DOMContentLoaded", () => {
    // DOM Elements
    const tabButtons = document.querySelectorAll(".tab-btn")
    const tabContents = document.querySelectorAll(".tab-content")
    const letterForm = document.getElementById("letter-form")
    const fileInput = document.getElementById("attachment")
    const fileName = document.getElementById("file-name")
    const searchBtn = document.getElementById("search-btn")
    const searchInput = document.getElementById("search-input")
    const statusFilter = document.getElementById("status-filter")
    const lettersContainer = document.getElementById("letters-container")
    const modal = document.getElementById("letter-detail-modal")
    const closeModal = document.querySelector(".close-modal")
  
    // Get user info
    getUserInfo()
  
    // Load letters history
    loadLetterHistory()
  
    // Event Listeners
    tabButtons.forEach((button) => {
      button.addEventListener("click", () => {
        const tabId = button.getAttribute("data-tab")
        switchTab(tabId)
      })
    })
  
    letterForm.addEventListener("submit", submitLetter)
  
    fileInput.addEventListener("change", function () {
      if (this.files.length > 0) {
        fileName.textContent = this.files[0].name
      } else {
        fileName.textContent = "No file chosen"
      }
    })
  
    searchBtn.addEventListener("click", () => {
      loadLetterHistory(searchInput.value.trim())
    })
  
    searchInput.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        loadLetterHistory(this.value.trim())
      }
    })
  
    statusFilter.addEventListener("change", function () {
      loadLetterHistory(searchInput.value.trim(), this.value)
    })
  
    closeModal.addEventListener("click", () => {
      modal.style.display = "none"
    })
  
    window.addEventListener("click", (e) => {
      if (e.target === modal) {
        modal.style.display = "none"
      }
    })
  
    // Form validation
    document.querySelectorAll("#letter-form input[required], #letter-form textarea[required]").forEach((field) => {
      field.addEventListener("blur", function () {
        validateField(this)
      })
  
      field.addEventListener("input", function () {
        clearValidation(this)
      })
    })
  
    // Functions
    function getUserInfo() {
      fetch("get_user_info.php")
        .then((response) => {
          if (!response.ok) {
            throw new Error("Network response was not ok")
          }
          return response.json()
        })
        .then((data) => {
          if (data.loggedIn) {
            document.getElementById("user-name").textContent = data.user_name
          } else {
            window.location.href = "../login.php"
          }
        })
        .catch((error) => {
          console.error("Error fetching user info:", error)
          showToast("Error checking user session", "error")
        })
    }
  
    function switchTab(tabId) {
      tabButtons.forEach((btn) => {
        if (btn.getAttribute("data-tab") === tabId) {
          btn.classList.add("active")
        } else {
          btn.classList.remove("active")
        }
      })
  
      tabContents.forEach((content) => {
        if (content.id === tabId) {
          content.classList.add("active")
        } else {
          content.classList.remove("active")
        }
      })
  
      // Reload letter history when switching to that tab
      if (tabId === "letter-history") {
        loadLetterHistory()
      }
    }
  
    function submitLetter(e) {
      e.preventDefault()
  
      // Validate form
      const subject = document.getElementById("subject")
      const message = document.getElementById("message")
      let isValid = true
  
      if (!validateField(subject)) isValid = false
      if (!validateField(message)) isValid = false
  
      if (!isValid) {
        showToast("Please fill in all required fields", "error")
        return
      }
  
      // Check file size if a file is selected
      if (fileInput.files.length > 0) {
        const file = fileInput.files[0]
        const fileSize = file.size / 1024 / 1024 // Convert to MB
        const allowedTypes = [
          "application/pdf",
          "application/msword",
          "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
          "image/jpeg",
          "image/png",
        ]
  
        if (fileSize > 5) {
          showToast("File size exceeds 5MB limit", "error")
          return
        }
  
        if (!allowedTypes.includes(file.type)) {
          showToast("Invalid file type. Please upload PDF, DOC, DOCX, JPG, or PNG", "error")
          return
        }
      }
  
      // Submit form using FormData to handle file upload
      const formData = new FormData(letterForm)
  
      // Show loading toast
      showToast("Sending your letter...", "info")
  
      fetch("submit_letter.php", {
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
            letterForm.reset()
            fileName.textContent = "No file chosen"
            showToast("Your letter has been sent successfully", "success")
  
            // Switch to history tab
            switchTab("letter-history")
          } else {
            showToast(data.error || "Error sending letter", "error")
          }
        })
        .catch((error) => {
          console.error("Error sending letter:", error)
          showToast("Error sending letter. Please try again.", "error")
        })
    }
  
    function loadLetterHistory(search = "", status = "all") {
      lettersContainer.innerHTML = '<div class="loading">Loading your letters...</div>'
  
      let url = "get_letters.php"
      const params = []
  
      if (search) params.push(`search=${encodeURIComponent(search)}`)
      if (status !== "all") params.push(`status=${encodeURIComponent(status)}`)
  
      if (params.length > 0) {
        url += "?" + params.join("&")
      }
  
      fetch(url)
        .then((response) => {
          if (!response.ok) {
            throw new Error("Network response was not ok")
          }
          return response.json()
        })
        .then((data) => {
          if (data.length === 0) {
            lettersContainer.innerHTML = '<div class="loading">No letters found.</div>'
            return
          }
  
          lettersContainer.innerHTML = ""
  
          data.forEach((letter) => {
            const letterItem = document.createElement("div")
            letterItem.className = "letter-item"
            letterItem.dataset.id = letter.id
  
            const statusClass = `status-${letter.status}`
            const hasAttachment = letter.attachment ? '<i class="fas fa-paperclip"></i>' : ""
  
            letterItem.innerHTML = `
              <div class="letter-info">
                <div class="letter-subject">${letter.subject} ${hasAttachment}</div>
                <div class="letter-meta">
                  <div class="letter-date"><i class="fas fa-calendar"></i> ${formatDate(letter.created_at)}</div>
                </div>
              </div>
              <div class="letter-status ${statusClass}">${letter.status}</div>
            `
  
            letterItem.addEventListener("click", () => viewLetterDetails(letter.id))
            lettersContainer.appendChild(letterItem)
          })
        })
        .catch((error) => {
          console.error("Error loading letters:", error)
          lettersContainer.innerHTML = '<div class="loading">Error loading letters. Please try again.</div>'
          showToast("Error loading letters", "error")
        })
    }
  
    function viewLetterDetails(id) {
      showToast("Loading letter details...", "info")
  
      fetch(`get_letter_details.php?id=${id}`)
        .then((response) => {
          // Check if the response is OK
          if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`)
          }
  
          // Try to parse the response as JSON
          return response.text().then((text) => {
            try {
              return JSON.parse(text)
            } catch (e) {
              console.error("Invalid JSON response:", text)
              throw new Error("Invalid JSON response from server")
            }
          })
        })
        .then((data) => {
          if (data.error) {
            showToast(data.error, "error")
            return
          }
  
          document.getElementById("modal-subject").textContent = data.subject
          document.getElementById("modal-date").textContent = formatDate(data.created_at)
          document.getElementById("modal-status").textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1)
          document.getElementById("modal-message").textContent = data.message
  
          // Handle attachment
          const attachmentContainer = document.getElementById("modal-attachment-container")
          if (data.attachment) {
            attachmentContainer.style.display = "block"
            const attachmentLink = document.getElementById("modal-attachment")
            attachmentLink.href = "uploads/" + data.attachment
            document.getElementById("modal-attachment-name").textContent = data.attachment.split("/").pop()
          } else {
            attachmentContainer.style.display = "none"
          }
  
          // Handle reply
          const replyContainer = document.getElementById("modal-reply-container")
          if (data.reply) {
            replyContainer.style.display = "block"
            document.getElementById("modal-reply").textContent = data.reply
          } else {
            replyContainer.style.display = "none"
          }
  
          modal.style.display = "block"
        })
        .catch((error) => {
          console.error("Error fetching letter details:", error)
          showToast("Error loading letter details. Check console for details.", "error")
        })
    }
  
    function validateField(field) {
      const id = field.id
      const value = field.value.trim()
      const validationMessage = document.getElementById(`${id}-validation`)
  
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
      const options = { year: "numeric", month: "long", day: "numeric", hour: "2-digit", minute: "2-digit" }
      return new Date(dateString).toLocaleDateString(undefined, options)
    }
  
    function showToast(message, type = "info") {
      const toast = document.createElement("div")
      toast.className = `toast ${type}`
  
      let icon = "info-circle"
      if (type === "success") icon = "check-circle"
      if (type === "error") icon = "exclamation-circle"
      if (type === "warning") icon = "exclamation-triangle"
  
      toast.innerHTML = `
        <i class="fas fa-${icon}"></i>
        <span>${message}</span>
      `
  
      document.getElementById("toast-container").appendChild(toast)
  
      setTimeout(() => {
        toast.remove()
      }, 3000)
    }
  })
  
  