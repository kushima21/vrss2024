const profile_optionMenu =document.querySelector(".profile"),
    profileBtn =profile_optionMenu .querySelector(".profileBtn"),
    profile_options =profile_optionMenu .querySelector(".profile_option"),
    profile_text = profile_optionMenu .querySelector(".profile_text");

    profileBtn.addEventListener ("click", () => profile_optionMenu  .classList.toggle("active"));

    profile_options.forEach(profile_option => {
        profile_option.addEventListener("click", () => {
            let selectedOption = option.querySelector (".profile_text").innerText;
            sBtn_text.innerText = selectedOption;
            
            profile_optionMenu  .classList.remove("active");
        })
    })

    const optionMenu =document.querySelector(".select-menu"),
    selectBtn =optionMenu.querySelector(".selectBtn"),
    options =optionMenu.querySelector(".option"),
    sBtn_text = optionMenu.querySelector(".sBtn_text");

    selectBtn.addEventListener ("click", () => optionMenu .classList.toggle("active"));

    options.forEach(option => {
        option.addEventListener("click", () => {
            let selectedOption = option.querySelector (".option_text").innerText;
            sBtn_text.innerText = selectedOption;
            
            optionMenu .classList.remove("active");
        })
    })
    document.querySelector('.profileBtn').addEventListener('click', function() {
        this.classList.toggle('active');
    });