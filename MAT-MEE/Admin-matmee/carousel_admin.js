let currentSlide = null

const strip = document.getElementById("strip")
const thumbs = document.querySelectorAll(".cm-thumb")

thumbs.forEach(t => {

t.onclick = () => {

thumbs.forEach(x=>x.classList.remove("active"))

t.classList.add("active")

currentSlide = t.dataset.id

loadPreview(currentSlide)

}

})

function loadPreview(id){

const slide = SLIDES.find(s => s.id == id)

if(!slide) return

document.querySelector(".cm-prev-bg").style.backgroundImage =
`url('../upload/carousel/${slide.image_path}')`

document.querySelector(".cm-prev-title").innerText =
slide.title

document.querySelector(".cm-prev-sub").innerText =
slide.subtitle

document.querySelector(".cm-prev-btn").innerText =
slide.button_text

}