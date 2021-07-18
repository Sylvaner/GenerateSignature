function showToast(msg) {
    const toast = document.getElementById('toast');
    toast.textContent = msg;
    toast.style.opacity = 1.0;
    toast.style.bottom = '2rem';
    setTimeout(() => {
        toast.style.opacity = 0;
        toast.style.bottom = 0;
    }, 2000);
}
