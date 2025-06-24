// Lấy danh sách địa điểm (venue) từ API và render vào select
function loadVenueList(selectedVenueId = null) {
    fetch('api/venue.php')
        .then(res => res.json())
        .then(data => {
            if (data.status && data.data && data.data.items) {
                const venues = data.data.items;
                const select = document.getElementById('conference-venue-input');
                if (!select) return;
                select.innerHTML = '';
                venues.forEach(venue => {
                    const option = document.createElement('option');
                    option.value = venue.id;
                    option.textContent = venue.name;
                    if (selectedVenueId && selectedVenueId == venue.id) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            }
        });
}
