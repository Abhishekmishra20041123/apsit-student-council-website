document.addEventListener("DOMContentLoaded", () => {
  // DOM Elements
  const profileNavItems = document.querySelectorAll(".profile-nav li[data-tab]")
  const tabContents = document.querySelectorAll(".tab-content")
  const accountForm = document.getElementById("account-form")
  const passwordForm = document.getElementById("password-form")
  const notificationsForm = document.getElementById("notifications-form")
  const logoutBtn = document.getElementById("logout-btn")
  const newPasswordInput = document.getElementById("new-password")
  const confirmPasswordInput = document.getElementById("confirm-password")
  const passwordStrengthMeter = document.getElementById("password-strength-meter")
  const passwordStrengthText = document.getElementById("password-strength-text")
  const toastContainer = document.querySelector('.toast-container')

  // Check if user is logged in and verified
  checkUserSession()

  // Load user profile data
  loadUserProfile()

  // Event Listeners
  profileNavItems.forEach((item) => {
    item.addEventListener("click", () => {
      const tabId = item.getAttribute("data-tab")
      switchTab(tabId)
    })
  })

  accountForm.addEventListener("submit", updateProfile)
  passwordForm.addEventListener("submit", updatePassword)
  notificationsForm.addEventListener("submit", updateNotifications)
  logoutBtn.addEventListener("click", logout)

  // Password strength checker
  newPasswordInput.addEventListener("input", checkPasswordStrength)

  // Password confirmation validator
  confirmPasswordInput.addEventListener("input", validatePasswordMatch)

  // Functions
  function checkUserSession() {
    fetch("get_user_info.php")
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok")
        }
        return response.json()
      })
      .then((data) => {
        if (!data.loggedIn) {
          window.location.href = "../login.html"
        } else if (!data.verified) {
          // Check if the user has verified their identity for profile access
          window.location.href = "verify.html"
        } else {
          document.getElementById("profile-name").querySelector("h3").textContent = data.user_name
        }
      })
      .catch((error) => {
        console.error("Error checking session:", error)
        showToast("Error checking user session", "error")
        window.location.href = "../login.html"
      })
  }

  function loadUserProfile() {
    fetch("get_profile.php")
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok")
        }
        return response.json()
      })
      .then((data) => {
        if (data.error) {
          showToast(data.error, "error")
          return
        }

        // Fill account form
        document.getElementById("name").value = data.name || ""
        document.getElementById("email").value = data.email || ""
        document.getElementById("department").value = data.department || ""
        document.getElementById("year").value = data.year || ""
        document.getElementById("bio").value = data.bio || ""

        // Load notifications
        loadNotifications()
      })
      .catch((error) => {
        console.error("Error loading profile:", error)
        showToast("Error loading profile data", "error")
      })
  }

  function loadNotifications() {
    console.log('Loading notifications...');
    fetch("get_notifications.php")
      .then((response) => {
        console.log('Response received:', response);
        if (!response.ok) {
          throw new Error("Network response was not ok")
        }
        return response.json()
      })
      .then((data) => {
        console.log('Notifications data:', data);
        if (data.error) {
          showToast(data.error, "error")
          console.error('Error:', data.error, 'Debug:', data.debug)
          return
        }

        const notificationList = document.getElementById("notification-list")
        if (!notificationList) {
          console.error('Notification list element not found')
          return
        }
        
        notificationList.innerHTML = ""

        if (!data.notifications || data.notifications.length === 0) {
          notificationList.innerHTML = '<p class="text-muted text-center">No notifications yet</p>'
          return
        }

        data.notifications.forEach((notification) => {
          console.log('Processing notification:', notification);
          const notificationItem = document.createElement("div")
          notificationItem.className = `notification-item ${notification.is_read ? "" : "unread"}`
          notificationItem.innerHTML = `
            <div class="notification-header">
              <h5 class="notification-title">${escapeHtml(notification.title)}</h5>
              <span class="notification-time">${formatTime(notification.created_at)}</span>
            </div>
            <div class="notification-content">
              ${escapeHtml(notification.message)}
            </div>
            <div class="notification-actions">
              ${!notification.is_read ? `<button class="mark-read" data-id="${notification.id}">Mark as Read</button>` : ""}
              <button class="delete" data-id="${notification.id}">Delete</button>
            </div>
          `
          notificationList.appendChild(notificationItem)
        })

        // Add event listeners for notification actions
        document.querySelectorAll(".mark-read").forEach((button) => {
          button.addEventListener("click", markNotificationAsRead)
        })

        document.querySelectorAll(".delete").forEach((button) => {
          button.addEventListener("click", deleteNotification)
        })
      })
      .catch((error) => {
        console.error("Error loading notifications:", error)
        showToast("Error loading notifications", "error")
      })
  }

  function markNotificationAsRead(e) {
    const notificationId = e.target.dataset.id
    fetch("mark_notification_read.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ notification_id: notificationId }),
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok")
        }
        return response.json()
      })
      .then((data) => {
        if (data.success) {
          showToast("Notification marked as read", "success")
          loadNotifications()
        } else {
          showToast(data.error || "Error marking notification as read", "error")
        }
      })
      .catch((error) => {
        console.error("Error marking notification as read:", error)
        showToast("Error marking notification as read", "error")
      })
  }

  function deleteNotification(e) {
    const notificationId = e.target.dataset.id
    fetch("delete_notification.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ notification_id: notificationId }),
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok")
        }
        return response.json()
      })
      .then((data) => {
        if (data.success) {
          showToast("Notification deleted", "success")
          loadNotifications()
        } else {
          showToast(data.error || "Error deleting notification", "error")
        }
      })
      .catch((error) => {
        console.error("Error deleting notification:", error)
        showToast("Error deleting notification", "error")
      })
  }

  function formatTime(timestamp) {
    const date = new Date(timestamp)
    return date.toLocaleString()
  }

  function switchTab(tabId) {
    // Update navigation
    profileNavItems.forEach((item) => {
      if (item.getAttribute("data-tab") === tabId) {
        item.classList.add("active")
      } else {
        item.classList.remove("active")
      }
    })

    // Update content
    tabContents.forEach((content) => {
      if (content.id === tabId) {
        content.classList.add("active")
      } else {
        content.classList.remove("active")
      }
    })
  }

  function updateProfile(e) {
    e.preventDefault()

    // Validate form
    const name = document.getElementById("name").value.trim()
    const email = document.getElementById("email").value.trim()
    let isValid = true

    if (!name) {
      document.getElementById("name-validation").textContent = "Name is required"
      document.getElementById("name").classList.add("is-invalid")
      isValid = false
    } else {
      document.getElementById("name").classList.remove("is-invalid")
    }

    if (!email) {
      document.getElementById("email-validation").textContent = "Email is required"
      document.getElementById("email").classList.add("is-invalid")
      isValid = false
    } else if (!/^\S+@\S+\.\S+$/.test(email)) {
      document.getElementById("email-validation").textContent = "Please enter a valid email address"
      document.getElementById("email").classList.add("is-invalid")
      isValid = false
    } else {
      document.getElementById("email").classList.remove("is-invalid")
    }

    if (!isValid) {
      return
    }

    // Create form data
    const formData = new FormData(accountForm)

    // Show loading toast
    showToast("Updating profile...", "info")

    fetch("update_profile.php", {
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
          showToast("Profile updated successfully", "success")
          document.getElementById("profile-name").querySelector("h3").textContent = name
        } else {
          showToast(data.error || "Error updating profile", "error")
        }
      })
      .catch((error) => {
        console.error("Error updating profile:", error)
        showToast("Error updating profile. Please try again.", "error")
      })
  }

  function updatePassword(e) {
    e.preventDefault()

    // Validate form
    const currentPassword = document.getElementById("current-password").value
    const newPassword = document.getElementById("new-password").value
    const confirmPassword = document.getElementById("confirm-password").value
    let isValid = true

    if (!currentPassword) {
      document.getElementById("current-password-validation").textContent = "Current password is required"
      document.getElementById("current-password").classList.add("is-invalid")
      isValid = false
    } else {
      document.getElementById("current-password").classList.remove("is-invalid")
    }

    if (!newPassword) {
      document.getElementById("new-password-validation").textContent = "New password is required"
      document.getElementById("new-password").classList.add("is-invalid")
      isValid = false
    } else if (getPasswordStrength(newPassword) < 3) {
      document.getElementById("new-password-validation").textContent = "Password is too weak"
      document.getElementById("new-password").classList.add("is-invalid")
      isValid = false
    } else {
      document.getElementById("new-password").classList.remove("is-invalid")
    }

    if (!confirmPassword) {
      document.getElementById("confirm-password-validation").textContent = "Please confirm your password"
      document.getElementById("confirm-password").classList.add("is-invalid")
      isValid = false
    } else if (newPassword !== confirmPassword) {
      document.getElementById("confirm-password-validation").textContent = "Passwords do not match"
      document.getElementById("confirm-password").classList.add("is-invalid")
      isValid = false
    } else {
      document.getElementById("confirm-password").classList.remove("is-invalid")
    }

    if (!isValid) {
      return
    }

    // Create form data
    const formData = new FormData(passwordForm)

    // Show loading toast
    showToast("Updating password...", "info")

    fetch("update_password.php", {
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
          showToast("Password updated successfully", "success")
          passwordForm.reset()
          resetPasswordStrength()
        } else {
          showToast(data.error || "Error updating password", "error")
        }
      })
      .catch((error) => {
        console.error("Error updating password:", error)
        showToast("Error updating password. Please try again.", "error")
      })
  }

  function updateNotifications(e) {
    e.preventDefault()

    // Create form data
    const formData = new FormData(notificationsForm)

    // Show loading toast
    showToast("Updating notification preferences...", "info")

    fetch("update_notifications.php", {
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
          showToast("Notification preferences updated successfully", "success")
        } else {
          showToast(data.error || "Error updating notification preferences", "error")
        }
      })
      .catch((error) => {
        console.error("Error updating notification preferences:", error)
        showToast("Error updating notification preferences. Please try again.", "error")
      })
  }

  function logout() {
    fetch("logout.php")
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok")
        }
        return response.json()
      })
      .then((data) => {
        if (data.success) {
          window.location.href = "../login.html"
        } else {
          showToast(data.error || "Error logging out", "error")
        }
      })
      .catch((error) => {
        console.error("Error logging out:", error)
        showToast("Error logging out. Please try again.", "error")
      })
  }

  function checkPasswordStrength() {
    const password = newPasswordInput.value
    const strength = getPasswordStrength(password)

    // Update strength meter
    passwordStrengthMeter.style.width = `${strength * 25}%`

    // Update color based on strength
    if (strength === 0) {
      passwordStrengthMeter.className = "progress-bar"
      passwordStrengthText.textContent = "None"
    } else if (strength === 1) {
      passwordStrengthMeter.className = "progress-bar bg-danger"
      passwordStrengthText.textContent = "Weak"
    } else if (strength === 2) {
      passwordStrengthMeter.className = "progress-bar bg-warning"
      passwordStrengthText.textContent = "Fair"
    } else if (strength === 3) {
      passwordStrengthMeter.className = "progress-bar bg-info"
      passwordStrengthText.textContent = "Good"
    } else {
      passwordStrengthMeter.className = "progress-bar bg-success"
      passwordStrengthText.textContent = "Strong"
    }

    // Update requirement checks
    updatePasswordRequirements(password)
  }

  function getPasswordStrength(password) {
    let strength = 0

    if (password.length >= 8) {
      strength++
    }

    if (/[A-Z]/.test(password) && /[a-z]/.test(password)) {
      strength++
    }

    if (/[0-9]/.test(password)) {
      strength++
    }

    if (/[^A-Za-z0-9]/.test(password)) {
      strength++
    }

    return strength
  }

  function updatePasswordRequirements(password) {
    // Length check
    if (password.length >= 8) {
      document.getElementById("length-check").innerHTML = '<i class="fas fa-check-circle"></i> At least 8 characters'
    } else {
      document.getElementById("length-check").innerHTML = '<i class="fas fa-times-circle"></i> At least 8 characters'
    }

    // Uppercase check
    if (/[A-Z]/.test(password)) {
      document.getElementById("uppercase-check").innerHTML =
        '<i class="fas fa-check-circle"></i> At least one uppercase letter'
    } else {
      document.getElementById("uppercase-check").innerHTML =
        '<i class="fas fa-times-circle"></i> At least one uppercase letter'
    }

    // Lowercase check
    if (/[a-z]/.test(password)) {
      document.getElementById("lowercase-check").innerHTML =
        '<i class="fas fa-check-circle"></i> At least one lowercase letter'
    } else {
      document.getElementById("lowercase-check").innerHTML =
        '<i class="fas fa-times-circle"></i> At least one lowercase letter'
    }

    // Number check
    if (/[0-9]/.test(password)) {
      document.getElementById("number-check").innerHTML = '<i class="fas fa-check-circle"></i> At least one number'
    } else {
      document.getElementById("number-check").innerHTML = '<i class="fas fa-times-circle"></i> At least one number'
    }

    // Special character check
    if (/[^A-Za-z0-9]/.test(password)) {
      document.getElementById("special-check").innerHTML =
        '<i class="fas fa-check-circle"></i> At least one special character'
    } else {
      document.getElementById("special-check").innerHTML =
        '<i class="fas fa-times-circle"></i> At least one special character'
    }
  }

  function validatePasswordMatch() {
    const newPassword = newPasswordInput.value
    const confirmPassword = confirmPasswordInput.value

    if (confirmPassword && newPassword !== confirmPassword) {
      document.getElementById("confirm-password-validation").textContent = "Passwords do not match"
      confirmPasswordInput.classList.add("is-invalid")
    } else {
      document.getElementById("confirm-password-validation").textContent = ""
      confirmPasswordInput.classList.remove("is-invalid")
    }
  }

  function resetPasswordStrength() {
    passwordStrengthMeter.style.width = "0%"
    passwordStrengthMeter.className = "progress-bar"
    passwordStrengthText.textContent = "None"

    document.getElementById("length-check").innerHTML = '<i class="fas fa-times-circle"></i> At least 8 characters'
    document.getElementById("uppercase-check").innerHTML =
      '<i class="fas fa-times-circle"></i> At least one uppercase letter'
    document.getElementById("lowercase-check").innerHTML =
      '<i class="fas fa-times-circle"></i> At least one lowercase letter'
    document.getElementById("number-check").innerHTML = '<i class="fas fa-times-circle"></i> At least one number'
    document.getElementById("special-check").innerHTML =
      '<i class="fas fa-times-circle"></i> At least one special character'
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

    toastContainer.appendChild(toast)

    // Trigger reflow
    toast.offsetHeight

    // Add show class for animation
    toast.classList.add("show")

    // Remove toast after animation
    setTimeout(() => {
      toast.classList.remove("show")
      setTimeout(() => {
        toastContainer.removeChild(toast)
      }, 300)
    }, 3000)
  }

  // Helper function to escape HTML
  function escapeHtml(unsafe) {
    return unsafe
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;")
  }

  // Add event listener for notifications tab
  document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');
    const notificationsTab = document.querySelector('[data-tab="notifications"]');
    if (notificationsTab) {
      console.log('Notifications tab found');
      notificationsTab.addEventListener('click', function() {
        console.log('Notifications tab clicked');
        loadNotifications();
      });
    } else {
      console.error('Notifications tab not found');
    }
    
    // Load notifications immediately if we're on the notifications tab
    if (window.location.hash === '#notifications') {
      console.log('Loading notifications on page load');
      loadNotifications();
    }
  })
})