// Обробка відправки контактної форми
document.addEventListener("DOMContentLoaded", () => {
  const contactForm = document.getElementById("contactForm")

  if (contactForm) {
    contactForm.addEventListener("submit", async (e) => {
      e.preventDefault()

      const submitButton = contactForm.querySelector('button[type="submit"]')
      const originalButtonText = submitButton.textContent
      submitButton.disabled = true
      submitButton.textContent = "Відправка..."

      // Збір даних форми
      const formData = {
        company: document.getElementById("company").value,
        name: document.getElementById("name").value,
        position: document.getElementById("position").value,
        email: document.getElementById("email").value,
        phone: document.getElementById("phone").value,
        quantity: document.getElementById("quantity").value,
        message: document.getElementById("message").value,
      }

      try {
        console.log("[v0] Sending form data:", formData)
        const response = await fetch("/SmartLock_store/api/submit-form.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(formData),
        })

        console.log("[v0] Response status:", response.status)
        const contentType = response.headers.get("content-type")
        console.log("[v0] Content-Type:", contentType)

        let result
        if (contentType && contentType.includes("application/json")) {
          result = await response.json()
        } else {
          const text = await response.text()
          console.error("[v0] Non-JSON response:", text)
          throw new Error("Сервер повернув некоректну відповідь")
        }

        console.log("[v0] Response data:", result)

        if (result.success) {
          // Успішна відправка
          showNotification("success", result.message)
          contactForm.reset()
        } else {
          // Помилка валідації або сервера
          console.error("[v0] Server error:", result)
          showNotification("error", result.message || "Виникла помилка при обробці запиту")
        }
      } catch (error) {
        console.error("[v0] Fetch error:", error)
        showNotification("error", "Виникла помилка при відправці форми. Спробуйте пізніше.")
      } finally {
        submitButton.disabled = false
        submitButton.textContent = originalButtonText
      }
    })
  }
})

// Функція для відображення сповіщень
function showNotification(type, message) {
  // Видалення попередніх сповіщень
  const existingNotification = document.querySelector(".notification")
  if (existingNotification) {
    existingNotification.remove()
  }

  // Створення нового сповіщення
  const notification = document.createElement("div")
  notification.className = `notification notification-${type}`
  notification.textContent = message

  // Стилі для сповіщення
  notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 9999;
        animation: slideIn 0.3s ease;
        max-width: 400px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    `

  if (type === "success") {
    notification.style.background = "#10B981"
  } else {
    notification.style.background = "#EF4444"
  }

  document.body.appendChild(notification)

  // Автоматичне видалення через 5 секунд
  setTimeout(() => {
    notification.style.animation = "slideOut 0.3s ease"
    setTimeout(() => notification.remove(), 300)
  }, 5000)
}

// CSS анімації для сповіщень
const style = document.createElement("style")
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`
document.head.appendChild(style)
