const body = document.body;
const toggle = document.getElementById('theme-toggle');

if (localStorage.getItem('theme') === 'dark') {
    body.classList.add('dark');
}

toggle.addEventListener('click', () => {
    body.classList.toggle('dark');

    if (body.classList.contains('dark')) {
        localStorage.setItem('theme', 'dark');
    } else {
        localStorage.removeItem('theme');
    }
});