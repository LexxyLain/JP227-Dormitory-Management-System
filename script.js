

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('updateModal');
    const closeBtn = document.querySelector('.close');
    const updateForm = document.getElementById('updateRoomForm');
    const updateButtons = document.querySelectorAll('.btn-update');

    // Open modal and populate form when update button is clicked
    updateButtons.forEach(button => {
        button.addEventListener('click', function() {
            const roomId = this.dataset.roomId;
            const roomNumber = this.dataset.roomNumber;
            const capacity = this.dataset.capacity;
            const price = this.dataset.price;
            const status = this.dataset.status;

            document.getElementById('room_id').value = roomId;
            document.getElementById('room_number').value = roomNumber;
            document.getElementById('capacity').value = capacity;
            document.getElementById('price_per_month').value = price;
            document.getElementById('status').value = status;

            modal.style.display = 'block';
        });
    });

    // Close modal when clicking the close button
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Handle form submission
    updateForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('action', 'edit');

        fetch('process_rooms.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert(data.message || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the room');
        });
    });
});

