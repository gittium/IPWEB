<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Skills</title>
</head>
<body>
    <h2>Select Skills for User ID = 1</h2>
    <form action="save_skills.php" method="POST">
        <input type="hidden" name="user_id" value="1">
        <?php
        // เชื่อมต่อฐานข้อมูล
        $conn = new mysqli("localhost", "root", "", "test2");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // ดึงทักษะที่ User ID = 1 มีอยู่แล้ว
        $user_id = 1;
        $user_skills = [];
        $sql_user = "SELECT skill_id FROM user_skills WHERE user_id = $user_id";
        $result_user = $conn->query($sql_user);
        while ($row_user = $result_user->fetch_assoc()) {
            $user_skills[] = $row_user['skill_id'];
        }

        // ดึงข้อมูลจากตาราง skills
        $sql = "SELECT skill_id, skill_name FROM skills";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $checked = in_array($row["skill_id"], $user_skills) ? "checked" : "";
                echo '<input type="checkbox" name="skills[]" value="' . $row["skill_id"] . '" ' . $checked . '> ' . $row["skill_name"] . '<br>';
            }
        } else {
            echo "No skills available";
        }
        $conn->close();
        ?>
        <br>
        <input type="submit" value="Save">
    </form>
</body>
</html>
