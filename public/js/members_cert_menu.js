function toggleMemberCertMenu(memberId) {
    const menu = document.getElementById('memberCertMenu' + memberId);
    document.querySelectorAll('[id^="memberCertMenu"]').forEach(m => {
        if (m.id !== 'memberCertMenu' + memberId) {
            m.style.display = 'none';
        }
    });
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('[onclick^="toggleMemberCertMenu"]') && !e.target.closest('[id^="memberCertMenu"]')) {
        document.querySelectorAll('[id^="memberCertMenu"]').forEach(m => {
            m.style.display = 'none';
        });
    }
});
