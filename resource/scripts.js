document.addEventListener("DOMContentLoaded", () => {
    // DOM Elements
    const helpDeskBtn = document.getElementById("help-desk-btn")
    const helpDeskForm = document.getElementById("help-desk-form")
    const contactForm = document.getElementById("contact-form")
  
    // Study Materials
    const filterSubject = document.getElementById("filter-subject")
    const uploadForm = document.getElementById("upload-form")
  
    // Workshops
    const registerBtns = document.querySelectorAll(".register-btn")
    const workshopModal = document.getElementById("workshop-registration")
    const workshopTitle = document.getElementById("workshop-title")
    const workshopForm = document.getElementById("workshop-form")
  
    // Tools
    const toolBtns = document.querySelectorAll(".tool-btn")
    const toolContainer = document.getElementById("tool-container")
  
    // Tabs
    const tabBtns = document.querySelectorAll(".tab-btn")
    const tabPanes = document.querySelectorAll(".tab-pane")
  
    // Event Listeners
    // Help Desk
    if (helpDeskBtn) {
      helpDeskBtn.addEventListener("click", () => {
        helpDeskForm.style.display = helpDeskForm.style.display === "none" ? "block" : "none"
      })
    }
  
    if (contactForm) {
      contactForm.addEventListener("submit", (e) => {
        e.preventDefault()
        const name = document.getElementById("contact-name").value
        const email = document.getElementById("contact-email").value
        const issue = document.getElementById("contact-issue").value
  
        fetch("help_desk.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: `name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&issue=${encodeURIComponent(issue)}`,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              showNotification("Your message has been sent. We will contact you soon.", "success")
              helpDeskForm.style.display = "none"
              contactForm.reset()
            } else {
              showNotification(data.message || "Failed to send message. Please try again.", "error")
            }
          })
          .catch((error) => {
            showNotification("An error occurred. Please try again.", "error")
            console.error("Error:", error)
          })
      })
    }
  
    // Study Materials
    if (filterSubject) {
      filterSubject.addEventListener("change", function () {
        const subject = this.value
  
        fetch(`get_materials.php?subject=${encodeURIComponent(subject)}`)
          .then((response) => response.text())
          .then((html) => {
            document.getElementById("materials-list").innerHTML = html
  
            // Re-attach event listeners to download buttons
            document.querySelectorAll(".download-material").forEach((btn) => {
              btn.addEventListener("click", function () {
                const materialId = this.getAttribute("data-id")
                window.location.href = `download_material.php?id=${materialId}`
              })
            })
          })
          .catch((error) => {
            console.error("Error:", error)
            showNotification("Failed to load materials. Please try again.", "error")
          })
      })
    }
  
    // Attach event listeners to download buttons
    document.querySelectorAll(".download-material").forEach((btn) => {
      btn.addEventListener("click", function () {
        const materialId = this.getAttribute("data-id")
        window.location.href = `download_material.php?id=${materialId}`
      })
    })
    
    registerBtns.forEach((btn) => {
      btn.addEventListener("click", function () {
        console.log("Register button clicked for workshop:", this.getAttribute("data-workshop"))
        const workshop = this.getAttribute("data-workshop")
        workshopTitle.textContent = workshop
        workshopModal.style.display = "block"
      })
    })
  
    // Ensure workshop modal is properly initialized
    const workshopModalCheck = document.getElementById("workshop-registration")
    if (workshopModalCheck) {
      console.log("Workshop modal found")
    } else {
      console.error("Workshop modal not found in the DOM")
    }
  
    // Check if register buttons exist
    const registerButtons = document.querySelectorAll(".register-btn")
    console.log("Found", registerButtons.length, "register buttons")
  
    // Fix the workshop registration form submission
    workshopForm.addEventListener("submit", (e) => {
      e.preventDefault()
      const name = document.getElementById("workshop-name").value
      const email = document.getElementById("workshop-email").value
      const studentId = document.getElementById("workshop-id").value
      const workshop = workshopTitle.textContent
  
      // Show loading message
      //showNotification("Processing registration...", "info")
  
      fetch("workshop_registration.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&student_id=${encodeURIComponent(studentId)}&workshop=${encodeURIComponent(workshop)}`,
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error("Network response was not ok")
          }
          return response.json()
        })
        .then((data) => {
          if (data.success) {
            showNotification(`Successfully registered for ${workshop}!`, "success")
            workshopModal.style.display = "none"
            workshopForm.reset()
          } else {
            showNotification(data.message || "Registration failed. Please try again.", "error")
          }
        })
        .catch((error) => {
          console.error("Error:", error)
          showNotification("Workshop not loaded. Please try again later.", "error")
        })
    })
  

    // Tools
    toolBtns.forEach((btn) => {
      btn.addEventListener("click", function () {
        const tool = this.getAttribute("data-tool")
        loadTool(tool)
      })
    })
  
    // Tabs
    tabBtns.forEach((btn) => {
      btn.addEventListener("click", function () {
        const tab = this.getAttribute("data-tab")
  
        // Remove active class from all buttons and panes
        tabBtns.forEach((b) => b.classList.remove("active"))
        tabPanes.forEach((p) => p.classList.remove("active"))
  
        // Add active class to clicked button and corresponding pane
        this.classList.add("active")
        document.getElementById(`${tab}-tab`).classList.add("active")
      })
    })
  
    // Close buttons
    document.querySelectorAll(".close").forEach((btn) => {
      btn.addEventListener("click", () => {
        workshopModal.style.display = "none"
      })
    })
  
    // Close modals when clicking outside
    window.addEventListener("click", (e) => {
      if (e.target === workshopModal) {
        workshopModal.style.display = "none"
      }
    })
  
    // Functions
    function loadTool(toolName) {
      toolContainer.style.display = "block"
      toolContainer.innerHTML = ""
  
      switch (toolName) {
        case "gpa":
          loadGpaCalculator()
          break
        case "timetable":
          loadTimetableGenerator()
          break
        case "planner":
          loadTaskPlanner()
          break
      }
    }
  
    function loadGpaCalculator() {
      toolContainer.innerHTML = `
              <h3>GPA Calculator</h3>
              <div class="gpa-form">
                  <table id="gpa-table">
                      <thead>
                          <tr>
                              <th>Course</th>
                              <th>Credits</th>
                              <th>Grade</th>
                          </tr>
                      </thead>
                      <tbody>
                          <tr>
                              <td><input type="text" class="course-name" placeholder="Course name"></td>
                              <td><input type="number" class="course-credits" min="1" max="5" value="3"></td>
                              <td>
                                  <select class="course-grade">
                                      <option value="4.0">A (4.0)</option>
                                      <option value="3.7">A- (3.7)</option>
                                      <option value="3.3">B+ (3.3)</option>
                                      <option value="3.0">B (3.0)</option>
                                      <option value="2.7">B- (2.7)</option>
                                      <option value="2.3">C+ (2.3)</option>
                                      <option value="2.0">C (2.0)</option>
                                      <option value="1.7">C- (1.7)</option>
                                      <option value="1.3">D+ (1.3)</option>
                                      <option value="1.0">D (1.0)</option>
                                      <option value="0.0">F (0.0)</option>
                                  </select>
                              </td>
                          </tr>
                      </tbody>
                  </table>
                  <button class="btn" id="add-course-btn">Add Course</button>
                  <button class="btn" id="calculate-gpa-btn">Calculate GPA</button>
                  <div class="gpa-result" id="gpa-result"></div>
              </div>
          `
  
      document.getElementById("add-course-btn").addEventListener("click", () => {
        const tbody = document.querySelector("#gpa-table tbody")
        const newRow = document.createElement("tr")
        newRow.innerHTML = `
                  <td><input type="text" class="course-name" placeholder="Course name"></td>
                  <td><input type="number" class="course-credits" min="1" max="5" value="3"></td>
                  <td>
                      <select class="course-grade">
                          <option value="4.0">A (4.0)</option>
                          <option value="3.7">A- (3.7)</option>
                          <option value="3.3">B+ (3.3)</option>
                          <option value="3.0">B (3.0)</option>
                          <option value="2.7">B- (2.7)</option>
                          <option value="2.3">C+ (2.3)</option>
                          <option value="2.0">C (2.0)</option>
                          <option value="1.7">C- (1.7)</option>
                          <option value="1.3">D+ (1.3)</option>
                          <option value="1.0">D (1.0)</option>
                          <option value="0.0">F (0.0)</option>
                      </select>
                  </td>
              `
        tbody.appendChild(newRow)
      })
  
      document.getElementById("calculate-gpa-btn").addEventListener("click", () => {
        const courses = document.querySelectorAll("#gpa-table tbody tr")
        let totalCredits = 0
        let totalPoints = 0
  
        courses.forEach((course) => {
          const credits = Number.parseFloat(course.querySelector(".course-credits").value)
          const grade = Number.parseFloat(course.querySelector(".course-grade").value)
  
          totalCredits += credits
          totalPoints += credits * grade
        })
  
        const gpa = totalPoints / totalCredits
        document.getElementById("gpa-result").textContent = `Your GPA: ${gpa.toFixed(2)}`
  
        // Save GPA calculation to database if user is logged in
        if (document.querySelector(".upload-container")) {
          const courseData = []
          courses.forEach((course) => {
            courseData.push({
              name: course.querySelector(".course-name").value,
              credits: course.querySelector(".course-credits").value,
              grade: course.querySelector(".course-grade").value,
            })
          })
  
          fetch("save_gpa_calculation.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",//hastag
            },
            body: JSON.stringify({
              courses: courseData,
              gpa: gpa.toFixed(2),
            }),
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.success) {
                showNotification("GPA calculation saved!", "success")
              }
            })
            .catch((error) => {
              console.error("Error saving GPA calculation:", error)
            })
        }
      })
    }
  
    function loadTimetableGenerator() {
      toolContainer.innerHTML = `
              <h3>Timetable Generator</h3>
              <p>Create your weekly schedule by adding your classes and activities.</p>
              <div class="form-group">
                  <label for="event-name">Event Name:</label>
                  <input type="text" id="event-name" placeholder="Class or activity name">
              </div>
              <div class="form-group">
                  <label for="event-day">Day:</label>
                  <select id="event-day">
                      <option value="Monday">Monday</option>
                      <option value="Tuesday">Tuesday</option>
                      <option value="Wednesday">Wednesday</option>
                      <option value="Thursday">Thursday</option>
                      <option value="Friday">Friday</option>
                      <option value="Saturday">Saturday</option>
                      <option value="Sunday">Sunday</option>
                  </select>
              </div>
              <div class="form-group">
                  <label for="event-time">Time:</label>
                  <input type="time" id="event-start-time"> to <input type="time" id="event-end-time">
              </div>
              <div class="form-group">
                  <label for="event-location">Location:</label>
                  <input type="text" id="event-location" placeholder="Building and room number">
              </div>
              <button class="btn" id="add-event-btn">Add to Timetable</button>
              
              <div id="timetable" class="timetable">
                  <h4>Your Weekly Schedule</h4>
                  <div id="timetable-content"></div>
              </div>
          `
  
      // Load saved timetable if user is logged in
      if (document.querySelector(".upload-container")) {
        fetch("get_timetable.php")
          .then((response) => response.json())
          .then((data) => {
            if (data.success && data.events.length > 0) {
              const timetableContent = document.getElementById("timetable-content")
              data.events.forEach((event) => {
                const eventElement = document.createElement("div")
                eventElement.className = "timetable-event"
                eventElement.setAttribute("data-event-id", event.id)
                eventElement.innerHTML = `
                              <h5>${event.name}</h5>
                              <p><strong>${event.day}</strong>: ${event.start_time} - ${event.end_time}</p>
                              <p>Location: ${event.location || "Not specified"}</p>
                              <button class="btn remove-event-btn">Remove</button>
                          `
                timetableContent.appendChild(eventElement)
              })
  
              // Add event listeners to remove buttons
              document.querySelectorAll(".remove-event-btn").forEach((btn) => {
                btn.addEventListener("click", function () {
                  const eventElement = this.closest(".timetable-event")
                  const eventId = eventElement.getAttribute("data-event-id")
                  removeEvent(eventId, eventElement)
                })
              })
            }
          })
          .catch((error) => {
            console.error("Error loading timetable:", error)
          })
      }
  
      document.getElementById("add-event-btn").addEventListener("click", () => {
        const name = document.getElementById("event-name").value
        const day = document.getElementById("event-day").value
        const startTime = document.getElementById("event-start-time").value
        const endTime = document.getElementById("event-end-time").value
        const location = document.getElementById("event-location").value
  
        if (!name || !startTime || !endTime) {
          showNotification("Please fill in all required fields", "error")
          return
        }
  
        // Save event to database if user is logged in
        if (document.querySelector(".upload-container")) {
          fetch("add_timetable_event.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `name=${encodeURIComponent(name)}&day=${encodeURIComponent(day)}&start_time=${encodeURIComponent(startTime)}&end_time=${encodeURIComponent(endTime)}&location=${encodeURIComponent(location)}`,
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.success) {
                const timetableContent = document.getElementById("timetable-content")
                const eventElement = document.createElement("div")
                eventElement.className = "timetable-event"
                eventElement.setAttribute("data-event-id", data.event_id)
                eventElement.innerHTML = `
                              <h5>${name}</h5>
                              <p><strong>${day}</strong>: ${startTime} - ${endTime}</p>
                              <p>Location: ${location || "Not specified"}</p>
                              <button class="btn remove-event-btn">Remove</button>
                          `
                timetableContent.appendChild(eventElement)
  
                // Add event listener to remove button
                eventElement.querySelector(".remove-event-btn").addEventListener("click", () => {
                  const eventId = eventElement.getAttribute("data-event-id")
                  removeEvent(eventId, eventElement)
                })
  
                // Clear form
                document.getElementById("event-name").value = ""
                document.getElementById("event-start-time").value = ""
                document.getElementById("event-end-time").value = ""
                document.getElementById("event-location").value = ""
  
                showNotification("Event added to timetable!", "success")
              } else {
                showNotification(data.message || "Failed to add event. Please try again.", "error")
              }
            })
            .catch((error) => {
              showNotification("An error occurred. Please try again.", "error")
              console.error("Error:", error)
            })
        } else {
          // If not logged in, just add to the UI
          const timetableContent = document.getElementById("timetable-content")
          const eventElement = document.createElement("div")
          eventElement.className = "timetable-event"
          eventElement.innerHTML = `
                      <h5>${name}</h5>
                      <p><strong>${day}</strong>: ${startTime} - ${endTime}</p>
                      <p>Location: ${location || "Not specified"}</p>
                      <button class="btn" onclick="this.parentElement.remove()">Remove</button>
                  `
          timetableContent.appendChild(eventElement)
  
          // Clear form
          document.getElementById("event-name").value = ""
          document.getElementById("event-start-time").value = ""
          document.getElementById("event-end-time").value = ""
          document.getElementById("event-location").value = ""
        }
      })
    }
  
    function removeEvent(eventId, eventElement) {
      fetch("remove_timetable_event.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `event_id=${encodeURIComponent(eventId)}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            eventElement.remove()
            showNotification("Event removed from timetable!", "success")
          } else {
            showNotification(data.message || "Failed to remove event. Please try again.", "error")
          }
        })
        .catch((error) => {
          showNotification("An error occurred. Please try again.", "error")
          console.error("Error:", error)
        })
    }
  
    function loadTaskPlanner() {
      toolContainer.innerHTML = `
              <h3>Task Planner</h3>
              <p>Keep track of your assignments and deadlines.</p>
              <div class="form-group">
                  <label for="task-name">Task Name:</label>
                  <input type="text" id="task-name" placeholder="Assignment or task name">
              </div>
              <div class="form-group">
                  <label for="task-course">Course:</label>
                  <input type="text" id="task-course" placeholder="Related course">
              </div>
              <div class="form-group">
                  <label for="task-deadline">Deadline:</label>
                  <input type="date" id="task-deadline" min="2025-04-30">
              </div>
              <div class="form-group">
                  <label for="task-priority">Priority:</label>
                  <select id="task-priority">
                      <option value="High">High</option>
                      <option value="Medium" selected>Medium</option>
                      <option value="Low">Low</option>
                  </select>
              </div>
              <button class="btn" id="add-task-btn">Add Task</button>
              
              <div id="task-list" class="task-list"></div>
          `
  
      // Load saved tasks if user is logged in
      if (document.querySelector(".upload-container")) {
        fetch("get_tasks.php")
          .then((response) => response.json())
          .then((data) => {
            if (data.success && data.tasks.length > 0) {
              const taskList = document.getElementById("task-list")
              data.tasks.forEach((task) => {
                const taskElement = document.createElement("div")
                taskElement.className = `task-item ${task.completed ? "completed" : ""}`
                taskElement.setAttribute("data-task-id", task.id)
                taskElement.innerHTML = `
                              <div class="task-info">
                                  <h4>${task.name}</h4>
                                  <p>Course: ${task.course || "Not specified"}</p>
                                  <p>Deadline: ${task.deadline}</p>
                                  <p>Priority: <span class="priority-${task.priority.toLowerCase()}">${task.priority}</span></p>
                              </div>
                              <div class="task-actions">
                                  <button class="btn complete-task-btn">${task.completed ? "Uncomplete" : "Complete"}</button>
                                  <button class="btn delete-task-btn">Delete</button>
                              </div>
                          `
                taskList.appendChild(taskElement)
              })
  
              // Add event listeners to task buttons
              document.querySelectorAll(".complete-task-btn").forEach((btn) => {
                btn.addEventListener("click", function () {
                  const taskElement = this.closest(".task-item")
                  const taskId = taskElement.getAttribute("data-task-id")
                  const completed = !taskElement.classList.contains("completed")
                  toggleTaskCompletion(taskId, taskElement, completed)
                })
              })
  
              document.querySelectorAll(".delete-task-btn").forEach((btn) => {
                btn.addEventListener("click", function () {
                  const taskElement = this.closest(".task-item")
                  const taskId = taskElement.getAttribute("data-task-id")
                  deleteTask(taskId, taskElement)
                })
              })
            }
          })
          .catch((error) => {
            console.error("Error loading tasks:", error)
          })
      }
  
      document.getElementById("add-task-btn").addEventListener("click", () => {
        const name = document.getElementById("task-name").value
        const course = document.getElementById("task-course").value
        const deadline = document.getElementById("task-deadline").value
        const priority = document.getElementById("task-priority").value
  
        if (!name || !deadline) {
          showNotification("Please fill in all required fields", "error")
          return
        }
  
        // Save task to database if user is logged in
        if (document.querySelector(".upload-container")) {
          fetch("add_task.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `name=${encodeURIComponent(name)}&course=${encodeURIComponent(course)}&deadline=${encodeURIComponent(deadline)}&priority=${encodeURIComponent(priority)}`,
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.success) {
                const taskList = document.getElementById("task-list")
                const taskElement = document.createElement("div")
                taskElement.className = "task-item"
                taskElement.setAttribute("data-task-id", data.task_id)
                taskElement.innerHTML = `
                              <div class="task-info">
                                  <h4>${name}</h4>
                                  <p>Course: ${course || "Not specified"}</p>
                                  <p>Deadline: ${deadline}</p>
                                  <p>Priority: <span class="priority-${priority.toLowerCase()}">${priority}</span></p>
                              </div>
                              <div class="task-actions">
                                  <button class="btn complete-task-btn">Complete</button>
                                  <button class="btn delete-task-btn">Delete</button>
                              </div>
                          `
                taskList.appendChild(taskElement)
  
                // Add event listeners to task buttons
                taskElement.querySelector(".complete-task-btn").addEventListener("click", () => {
                  const taskId = taskElement.getAttribute("data-task-id")
                  const completed = !taskElement.classList.contains("completed")
                  toggleTaskCompletion(taskId, taskElement, completed)
                })
  
                taskElement.querySelector(".delete-task-btn").addEventListener("click", () => {
                  const taskId = taskElement.getAttribute("data-task-id")
                  deleteTask(taskId, taskElement)
                })
  
                // Clear form
                document.getElementById("task-name").value = ""
                document.getElementById("task-course").value = ""
                document.getElementById("task-deadline").value = ""
                document.getElementById("task-priority").value = "Medium"
  
                showNotification("Task added!", "success")
              } else {
                showNotification(data.message || "Failed to add task. Please try again.", "error")
              }
            })
            .catch((error) => {
              showNotification("An error occurred. Please try again.", "error")
              console.error("Error:", error)
            })
        } else {
          // If not logged in, just add to the UI
          const taskList = document.getElementById("task-list")
          const taskElement = document.createElement("div")
          taskElement.className = "task-item"
          taskElement.innerHTML = `
                      <div class="task-info">
                          <h4>${name}</h4>
                          <p>Course: ${course || "Not specified"}</p>
                          <p>Deadline: ${deadline}</p>
                          <p>Priority: <span class="priority-${priority.toLowerCase()}">${priority}</span></p>
                      </div>
                      <div class="task-actions">
                          <button class="btn" onclick="this.closest('.task-item').classList.toggle('completed')">Complete</button>
                          <button class="btn" onclick="this.closest('.task-item').remove()">Delete</button>
                      </div>
                  `
          taskList.appendChild(taskElement)
  
          // Clear form
          document.getElementById("task-name").value = ""
          document.getElementById("task-course").value = ""
          document.getElementById("task-deadline").value = ""
          document.getElementById("task-priority").value = "Medium"
        }
      })
    }
  
    function toggleTaskCompletion(taskId, taskElement, completed) {
      fetch("toggle_task_completion.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `task_id=${encodeURIComponent(taskId)}&completed=${completed ? 1 : 0}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            if (completed) {
              taskElement.classList.add("completed")
              taskElement.querySelector(".complete-task-btn").textContent = "Uncomplete"
            } else {
              taskElement.classList.remove("completed")
              taskElement.querySelector(".complete-task-btn").textContent = "Complete"
            }
            showNotification(`Task marked as ${completed ? "completed" : "uncompleted"}!`, "success")
          } else {
            showNotification(data.message || "Failed to update task. Please try again.", "error")
          }
        })
        .catch((error) => {
          showNotification("An error occurred. Please try again.", "error")
          console.error("Error:", error)
        })
    }
  
    function deleteTask(taskId, taskElement) {
      fetch("delete_task.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `task_id=${encodeURIComponent(taskId)}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            taskElement.remove()
            showNotification("Task deleted!", "success")
          } else {
            showNotification(data.message || "Failed to delete task. Please try again.", "error")
          }
        })
        .catch((error) => {
          showNotification("An error occurred. Please try again.", "error")
          console.error("Error:", error)
        })
    }
  
    function showNotification(message, type = "success") {
      let notificationContainer = document.getElementById("notification-container");
      
      if (!notificationContainer) {
        notificationContainer = document.createElement("div");
        notificationContainer.id = "notification-container";
        document.body.appendChild(notificationContainer);
      }
    
      const notification = document.createElement("div");
      notification.className = `notification ${type}`;
      notification.innerHTML = `
        <p>${message}</p>
        <button class="close-notification">&times;</button>
      `;
    
      notificationContainer.appendChild(notification);
    
      notification.querySelector(".close-notification").addEventListener("click", () => {
        notification.remove();
      });
    
      setTimeout(() => {
        notification.remove();
      }, 5000);
    }
    
    
    // Additional event listeners for other features
    document.getElementById("tutorials-link")?.addEventListener("click", (e) => {
      e.preventDefault();
      console.log("Tutorials link clicked");
      // You could load tutorials content via AJAX here
      showNotification("Tutorials are being loaded...", "info");
  });
  
  
    document.getElementById("internships-link")?.addEventListener("click", (e) => {
      e.preventDefault()
      console.log("Internships link clicked")
      showNotification("Loading internship opportunities...", "info")
    })
  
    document.getElementById("scholarships-link")?.addEventListener("click", (e) => {
      e.preventDefault()
      console.log("Scholarships link clicked")
      showNotification("Loading scholarship information...", "info")
    })
  
    document.getElementById("workshops-link")?.addEventListener("click", (e) => {
      e.preventDefault()
      //console.log("Workshops link clicked")
      //showNotification("Loading workshop details...", "info")
    })
  
    document.getElementById("mentorship-link")?.addEventListener("click", (e) => {
      e.preventDefault()
      console.log("Mentorship link clicked")
      showNotification("Loading mentorship programs...", "info")
    })
  
    document.getElementById("subject-resources-link")?.addEventListener("click", (e) => {
      e.preventDefault()
      console.log("Subject resources link clicked")
      showNotification("Loading subject resources...", "info")
    })
  
    // Support section buttons
    const findMentorBtn = document.getElementById("find-mentor-btn")
    if (findMentorBtn) {
        // Remove any existing click handlers
        findMentorBtn.onclick = null;
        // Remove any existing event listeners
        const newFindMentorBtn = findMentorBtn.cloneNode(true);
        findMentorBtn.parentNode.replaceChild(newFindMentorBtn, findMentorBtn);
        // Add our click handler
        newFindMentorBtn.addEventListener("click", (e) => {
            e.preventDefault();
            e.stopPropagation();
            window.open("mentoring.pdf", "_blank");
        }, true);
    }
  
    document.getElementById("book-tutor-btn")?.addEventListener("click", () => {
      showNotification("Tutor booking service will be available soon!", "info")
    })
  
    document.getElementById("join-group-btn")?.addEventListener("click", () => {
      showNotification("Study group directory will be available soon!", "info")
    })
  
    // Initialize any additional functionality
    initializeAdditionalFeatures()
  })
  
  // Additional initialization function
  function initializeAdditionalFeatures() {
    // Check if user is logged in
    const isLoggedIn = document.querySelector(".upload-container") !== null
  
    // Add any additional features that should be initialized on page load
    if (isLoggedIn) {
      console.log("User is logged in, initializing personalized features")
      // You could load user-specific data here
    } else {
      console.log("User is not logged in")
      // You could show login prompts or limited functionality notices
    }
  
    // Add responsive menu toggle for mobile
    const menuToggle = document.createElement("button")
    menuToggle.className = "menu-toggle"
    menuToggle.innerHTML = "☰"
    menuToggle.style.display = "none"
  
    // Only add mobile menu if on small screen
    if (window.innerWidth < 768) {
      const nav = document.querySelector("nav")
      if (nav) {
        menuToggle.style.display = "block"
        document.body.insertBefore(menuToggle, nav)
        nav.classList.add("mobile-hidden")
  
        menuToggle.addEventListener("click", () => {
          nav.classList.toggle("mobile-hidden")
        })
      }
    }
  
    // Add window resize listener for responsive behavior
    window.addEventListener("resize", () => {
      const nav = document.querySelector("nav")
      if (window.innerWidth < 768) {
        if (nav && !document.querySelector(".menu-toggle")) {
          const menuToggle = document.createElement("button")
          menuToggle.className = "menu-toggle"
          menuToggle.innerHTML = "☰"
          document.body.insertBefore(menuToggle, nav)
          nav.classList.add("mobile-hidden")
  
          menuToggle.addEventListener("click", () => {
            nav.classList.toggle("mobile-hidden")
          })
        }
      } else {
        const menuToggle = document.querySelector(".menu-toggle")
        if (menuToggle) {
          menuToggle.remove()
        }
        if (nav) {
          nav.classList.remove("mobile-hidden")
        }
      }
    })
  }
  
  // Execute code to test
  console.log("College Resources script loaded successfully");