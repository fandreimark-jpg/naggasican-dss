// resources/js/search-filter.js
//
// One generic search box for any table on the site. Every page used to
// write its own copy of this same "hide rows that don't match" logic
// (adviser students, sections, admin students, subjects, users) —
// now they all just call initTableSearch() with their own element IDs.

window.initTableSearch = function (searchInputId, rowSelector, noResultsId) {
    const input = document.getElementById(searchInputId);
    if (!input) return;

    input.addEventListener("input", function () {
        const query = this.value.toLowerCase();
        const rows = document.querySelectorAll(rowSelector);
        let visibleCount = 0;

        rows.forEach(function (row) {
            const matches = row.textContent.toLowerCase().includes(query);
            row.style.display = matches ? "" : "none";
            if (matches) visibleCount++;
        });

        if (noResultsId) {
            const noResults = document.getElementById(noResultsId);
            if (noResults) noResults.classList.toggle("hidden", visibleCount > 0);
        }
    });
};