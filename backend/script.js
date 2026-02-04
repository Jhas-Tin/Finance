// OPEN MODAL
function openAddStudentModal() {
    document.getElementById("addStudentModal").style.display = "flex";
}

// CLOSE MODAL
function closeModal() {
    document.getElementById("addStudentModal").style.display = "none";
}

document.querySelector(".close-btn").onclick = closeModal;
document.querySelector(".cancel-btn").onclick = closeModal;


// SAVE BUTTON FUNCTION
document.querySelector(".save-btn").addEventListener("click", function () {

    const form = document.getElementById("addStudentForm");

    // ✅ Basic form validation
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);

    fetch("add_student.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        data = data.trim(); // remove spaces/newlines

        if (data === "success") {
            alert("Student added successfully!");
            form.reset();
            closeModal();
            location.reload();
        } 
        else if (data === "exists") {
            alert("Student already exists in the system!");
        } 
        else {
            alert("Error adding student.");
            console.log("Server response:", data);
        }
    })
    .catch(error => {
        console.error("Fetch error:", error);
        alert("Connection error. Try again.");
    });

});