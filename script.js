// Import Three.js
const THREE = window.THREE

// 3D Lock Model with Three.js
function init3DModel() {
  const canvas = document.getElementById("canvas3d")
  if (!canvas) return

  const scene = new THREE.Scene()
  const camera = new THREE.PerspectiveCamera(45, canvas.clientWidth / canvas.clientHeight, 0.1, 1000)
  const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true })

  renderer.setSize(canvas.clientWidth, canvas.clientHeight)
  renderer.setClearColor(0xffffff, 0)

  const ambientLight = new THREE.AmbientLight(0xffffff, 1.2)
  scene.add(ambientLight)

  const directionalLight1 = new THREE.DirectionalLight(0xffffff, 1.5)
  directionalLight1.position.set(5, 5, 5)
  scene.add(directionalLight1)

  const directionalLight2 = new THREE.DirectionalLight(0xffffff, 1)
  directionalLight2.position.set(-5, 3, -5)
  scene.add(directionalLight2)

  const directionalLight3 = new THREE.DirectionalLight(0xffffff, 0.8)
  directionalLight3.position.set(0, -5, 0)
  scene.add(directionalLight3)

  const directionalLight4 = new THREE.DirectionalLight(0xffffff, 0.6)
  directionalLight4.position.set(0, 0, -10)
  scene.add(directionalLight4)

  const lockGroup = new THREE.Group()

  const gltfPath = "/smartlock.gltf"

  if (window.THREE.GLTFLoader) {
    const loader = new THREE.GLTFLoader()

    console.log("[v0] Attempting to load GLTF from:", gltfPath)

    // Show loading state
    const loadingDiv = document.createElement("div")
    loadingDiv.className = "loading-overlay"
    loadingDiv.innerHTML = '<div class="loading-spinner"></div>'
    canvas.parentElement.appendChild(loadingDiv)

    loader.load(
      gltfPath,
      (gltf) => {
        // GLTF loaded successfully with all colors and materials preserved
        const model = gltf.scene

        console.log("[v0] GLTF model loaded successfully!", model)

        // Center and scale the model
        const box = new THREE.Box3().setFromObject(model)
        const center = box.getCenter(new THREE.Vector3())
        model.position.sub(center)

        // Scale to fit
        const size = box.getSize(new THREE.Vector3())
        const maxDim = Math.max(size.x, size.y, size.z)
        const scale = 3 / maxDim
        model.scale.set(scale, scale, scale)

        lockGroup.add(model)
        loadingDiv.remove()
      },
      (xhr) => {
        const progress = Math.round((xhr.loaded / xhr.total) * 100)
        console.log("[v0] Loading GLTF: " + progress + "%")
      },
      (error) => {
        console.error("[v0] Error loading GLTF model:", error)
        console.error("[v0] File path attempted:", gltfPath)
        console.error("[v0] Please ensure smartlock.gltf is in the public/ folder")

        // Fallback to procedural model
        createProceduralLock(lockGroup)
        const loadingDiv = canvas.parentElement.querySelector(".loading-overlay")
        if (loadingDiv) loadingDiv.remove()
      },
    )
  } else {
    // GLTFLoader not available, use procedural model
    console.error("[v0] GLTFLoader not available, using procedural model")
    createProceduralLock(lockGroup)
  }

  scene.add(lockGroup)
  camera.position.z = 6

  // Mouse interaction
  let isDragging = false
  let previousMousePosition = { x: 0, y: 0 }
  const rotation = { x: 0, y: 0 }

  canvas.addEventListener("mousedown", () => {
    isDragging = true
  })
  canvas.addEventListener("mouseup", () => {
    isDragging = false
  })
  canvas.addEventListener("mouseleave", () => {
    isDragging = false
  })

  canvas.addEventListener("mousemove", (e) => {
    if (isDragging) {
      const deltaMove = {
        x: e.offsetX - previousMousePosition.x,
        y: e.offsetY - previousMousePosition.y,
      }

      rotation.y += deltaMove.x * 0.01
      rotation.x += deltaMove.y * 0.01
    }

    previousMousePosition = {
      x: e.offsetX,
      y: e.offsetY,
    }
  })

  // Touch support
  canvas.addEventListener("touchstart", (e) => {
    isDragging = true
    previousMousePosition = {
      x: e.touches[0].clientX,
      y: e.touches[0].clientY,
    }
  })

  canvas.addEventListener("touchend", () => {
    isDragging = false
  })

  canvas.addEventListener("touchmove", (e) => {
    if (isDragging) {
      const deltaMove = {
        x: e.touches[0].clientX - previousMousePosition.x,
        y: e.touches[0].clientY - previousMousePosition.y,
      }

      rotation.y += deltaMove.x * 0.01
      rotation.x += deltaMove.y * 0.01
    }

    previousMousePosition = {
      x: e.touches[0].clientX,
      y: e.touches[0].clientY,
    }
  })

  // Animation
  function animate() {
    requestAnimationFrame(animate)

    if (!isDragging) {
      rotation.y += 0.005
    }

    lockGroup.rotation.x = rotation.x
    lockGroup.rotation.y = rotation.y

    renderer.render(scene, camera)
  }

  animate()

  // Handle window resize
  window.addEventListener("resize", () => {
    if (canvas) {
      camera.aspect = canvas.clientWidth / canvas.clientHeight
      camera.updateProjectionMatrix()
      renderer.setSize(canvas.clientWidth, canvas.clientHeight)
    }
  })
}

function createProceduralLock(lockGroup) {
  // Main body
  const bodyGeometry = new THREE.BoxGeometry(2, 3, 0.8)
  const bodyMaterial = new THREE.MeshStandardMaterial({
    color: 0x6a6a6a,
    metalness: 0.7,
    roughness: 0.3,
  })
  const body = new THREE.Mesh(bodyGeometry, bodyMaterial)
  lockGroup.add(body)

  // Screen
  const screenGeometry = new THREE.BoxGeometry(1.6, 1.2, 0.05)
  const screenMaterial = new THREE.MeshStandardMaterial({
    color: 0x4169ff,
    emissive: 0x4169ff,
    emissiveIntensity: 0.5,
  })
  const screen = new THREE.Mesh(screenGeometry, screenMaterial)
  screen.position.set(0, 0.5, 0.41)
  lockGroup.add(screen)

  // Keypad buttons
  for (let i = 0; i < 3; i++) {
    for (let j = 0; j < 3; j++) {
      const buttonGeometry = new THREE.CylinderGeometry(0.15, 0.15, 0.1, 32)
      const buttonMaterial = new THREE.MeshStandardMaterial({
        color: 0x5a5a5a,
        metalness: 0.6,
        roughness: 0.3,
      })
      const button = new THREE.Mesh(buttonGeometry, buttonMaterial)
      button.position.set((i - 1) * 0.5, -0.6 - j * 0.4, 0.45)
      button.rotation.x = Math.PI / 2
      lockGroup.add(button)
    }
  }

  // Handle
  const handleGeometry = new THREE.TorusGeometry(0.6, 0.12, 16, 32, Math.PI)
  const handleMaterial = new THREE.MeshStandardMaterial({
    color: 0x707070,
    metalness: 0.9,
    roughness: 0.1,
  })
  const handle = new THREE.Mesh(handleGeometry, handleMaterial)
  handle.position.set(0, -0.5, 0.5)
  handle.rotation.x = Math.PI / 2
  lockGroup.add(handle)

  // LED indicator
  const ledGeometry = new THREE.SphereGeometry(0.08, 32, 32)
  const ledMaterial = new THREE.MeshStandardMaterial({
    color: 0x4169ff,
    emissive: 0x4169ff,
    emissiveIntensity: 1,
  })
  const led = new THREE.Mesh(ledGeometry, ledMaterial)
  led.position.set(0.7, 1.2, 0.45)
  lockGroup.add(led)
}

// Form submission
const contactForm = document.getElementById("contactForm")
if (contactForm) {
  contactForm.addEventListener("submit", (e) => {
    e.preventDefault()

    // Get form data
    const formData = new FormData(contactForm)
    const data = Object.fromEntries(formData)

    // Here you would normally send data to your server
    console.log("Form data:", data)

    // Show success message
    alert("Дякуємо за ваш запит! Ми зв'яжемося з вами найближчим часом.")

    // Reset form
    contactForm.reset()
  })
}

// Optional: Add event listeners for model-viewer
const modelViewer = document.getElementById("model3d")
if (modelViewer) {
  modelViewer.addEventListener("load", () => {
    console.log("3D модель успішно завантажена!")
  })

  modelViewer.addEventListener("error", (event) => {
    console.error("Помилка завантаження моделі:", event)
    console.log("Переконайтеся, що файл smartlock.glb знаходиться в папці public/")
  })
}

// Initialize 3D model when page loads
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", init3DModel)
} else {
  init3DModel()
}
