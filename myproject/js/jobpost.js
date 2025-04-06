function addImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            // สร้าง container สำหรับรูปภาพ
            const container = document.createElement('div');
            container.classList.add('image-container');

            // สร้าง element รูปภาพ
            const img = document.createElement('img');
            img.src = e.target.result;
            img.alt = "Uploaded Image";
            img.onclick = () => selectImage(img); // คลิกเพื่อเลือก
            container.appendChild(img);

            // สร้างปุ่มลบ
            const deleteBtn = document.createElement('button');
            deleteBtn.classList.add('delete-btn');
            deleteBtn.innerHTML = "&times;";
            deleteBtn.onclick = () => container.remove(); // คลิกเพื่อลบรูป
            container.appendChild(deleteBtn);

            // เพิ่ม container ใน .images
            document.querySelector('.images').appendChild(container);
        };
        reader.readAsDataURL(file);
    }
}

function selectImage(imgElement) {
    // ดึงค่าพาธของรูปภาพ
    let imagePath = imgElement.getAttribute("src");

    // ตั้งค่าค่าของ input hidden ให้เป็นพาธของภาพ
    document.getElementById("selectedImagePath").value = imagePath;

    // ลบคลาส active จากรูปอื่น
    document.querySelectorAll(".images img").forEach(img => img.classList.remove("active"));

    // เพิ่มคลาส active ให้รูปที่ถูกเลือก
    imgElement.classList.add("active");
}


function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.alt = "Uploaded Image";
            img.style.width = "180px";
            img.style.height = "135px";
            img.style.objectFit = "cover";
            img.style.borderRadius = "8px";

            const addPhoto = document.querySelector('.add-photo');
            addPhoto.insertAdjacentElement('beforebegin', img);
        };
        reader.readAsDataURL(file);
    }
}
function toggleImageActive(img) {
    const images = document.querySelectorAll('.images img');
    images.forEach(image => image.classList.remove('active'));
    img.classList.add('active');
}

document.getElementById("category-select").addEventListener("change", function () {
    const categoryId = this.value;
    const jobSubSelect = document.getElementById("job-sub-select");
    const jobSubContainer = document.getElementById("job-sub-container");

    // ล้างตัวเลือกเดิม
    jobSubSelect.innerHTML = '<option value="">-- เลือกงานย่อย --</option>';

    if (categoryId) {
        fetch(`get_job_subcategories.php?category_id=${categoryId}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    data.forEach(sub => {
                        const option = document.createElement("option");
                        option.value = sub.id;
                        option.textContent = sub.name;
                        jobSubSelect.appendChild(option);
                    });

                    // แสดง dropdown งานย่อย
                    jobSubContainer.style.display = "block";
                } else {
                    jobSubContainer.style.display = "none";
                }
            })
            .catch(error => console.error('Error:', error));
    } else {
        jobSubContainer.style.display = "none";
    }
});

document.getElementById("job-end").addEventListener("change", function () {
    const startTime = document.getElementById("job-start").value;
    const endTime = this.value;

    if (startTime && endTime && endTime <= startTime) {
        alert("เวลาสิ้นสุดต้องมากกว่าเวลาเริ่มต้น!");
        this.value = ""; // ล้างค่าเวลาสิ้นสุด
    }
});

document.getElementById("reward-type-select").addEventListener("change", function () {
    const rewardType = this.value;
    const rewardInputContainer = document.getElementById("reward-input-container");
    const rewardLabelText = document.getElementById("reward-label-text");
    const rewardUnit = document.getElementById("reward-unit");
    const rewardInput = document.getElementById("reward-input");

    if (rewardType) {
        // ถ้าผู้ใช้เลือกประเภทเป็น "เงิน"
        if (rewardType == "1") {
            rewardLabelText.textContent = "Amount/จำนวนเงิน";
            rewardUnit.textContent = "บาท";
        }
        // ถ้าผู้ใช้เลือกประเภทเป็น "ชั่วโมงประสบการณ์"
        else if (rewardType == "2") {
            rewardLabelText.textContent = "Hours/จำนวนชั่วโมง";
            rewardUnit.textContent = "ชั่วโมง";
        }

        // แสดงช่องกรอกค่าตามประเภทที่เลือก
        rewardInputContainer.style.display = "block";
    } else {
        // ซ่อนช่องกรอกค่าหากไม่ได้เลือกประเภทผลตอบแทน
        rewardInputContainer.style.display = "none";
    }
});













