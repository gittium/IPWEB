<?php
include 'db_connect.php';
header('Content-Type: application/json');

if(isset($_POST['action'])){
    $action = $_POST['action'];

    // Skill & Subskill
    if($action == 'add_skill'){
        $skill_name = trim($_POST['skill_name']);
        if(empty($skill_name)){
            echo json_encode(['success' => false, 'error' => 'ชื่อทักษะห้ามว่าง']);
            exit;
        }
        $skill_name = $conn->real_escape_string($skill_name);
        $sql = "INSERT INTO skill (skill_name) VALUES ('$skill_name')";
        if($conn->query($sql)){
            echo json_encode(['success' => true, 'skill_id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        exit;
    }
    else if($action == 'add_subskill'){
        $subskill_name = trim($_POST['subskill_name']);
        $skill_id = intval($_POST['skill_id']);
        if(empty($subskill_name) || $skill_id <= 0){
            echo json_encode(['success' => false, 'error' => 'ข้อมูลไม่ครบ']);
            exit;
        }
        $subskill_name = $conn->real_escape_string($subskill_name);
        $sql = "INSERT INTO subskill (subskill_name, skill_id) VALUES ('$subskill_name', '$skill_id')";
        if($conn->query($sql)){
            echo json_encode(['success' => true, 'subskill_id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        exit;
    }
    else if($action == 'edit_skill'){
        $id = intval($_POST['id']);
        $new_name = trim($_POST['name']);
        if($id <= 0 || empty($new_name)){
            echo json_encode(['success' => false, 'error' => 'ข้อมูลไม่ครบ']);
            exit;
        }
        $new_name = $conn->real_escape_string($new_name);
        $sql = "UPDATE skill SET skill_name='$new_name' WHERE skill_id='$id'";
        if($conn->query($sql)){
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        exit;
    }
    else if($action == 'edit_subskill'){
        $id = intval($_POST['id']);
        $new_name = trim($_POST['name']);
        if($id <= 0 || empty($new_name)){
            echo json_encode(['success' => false, 'error' => 'ข้อมูลไม่ครบ']);
            exit;
        }
        $new_name = $conn->real_escape_string($new_name);
        $sql = "UPDATE subskill SET subskill_name='$new_name' WHERE subskill_id='$id'";
        if($conn->query($sql)){
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        exit;
    }

    // Hobby & Subhobby
    else if($action == 'add_hobby'){
        $hobby_name = trim($_POST['hobby_name']);
        if(empty($hobby_name)){
            echo json_encode(['success' => false, 'error' => 'ชื่องานอดิเรกห้ามว่าง']);
            exit;
        }
        $hobby_name = $conn->real_escape_string($hobby_name);
        $sql = "INSERT INTO hobby (hobby_name) VALUES ('$hobby_name')";
        if($conn->query($sql)){
            echo json_encode(['success' => true, 'hobby_id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        exit;
    }
    else if($action == 'add_subhobby'){
        $subhobby_name = trim($_POST['subhobby_name']);
        $hobby_id = intval($_POST['hobby_id']);
        if(empty($subhobby_name) || $hobby_id <= 0){
            echo json_encode(['success' => false, 'error' => 'ข้อมูลไม่ครบ']);
            exit;
        }
        $subhobby_name = $conn->real_escape_string($subhobby_name);
        $sql = "INSERT INTO subhobby (subhobby_name, hobby_id) VALUES ('$subhobby_name', '$hobby_id')";
        if($conn->query($sql)){
            echo json_encode(['success' => true, 'subhobby_id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        exit;
    }
    else if($action == 'edit_hobby'){
        $id = intval($_POST['id']);
        $new_name = trim($_POST['name']);
        if($id <= 0 || empty($new_name)){
            echo json_encode(['success' => false, 'error' => 'ข้อมูลไม่ครบ']);
            exit;
        }
        $new_name = $conn->real_escape_string($new_name);
        $sql = "UPDATE hobby SET hobby_name='$new_name' WHERE hobby_id='$id'";
        if($conn->query($sql)){
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        exit;
    }
    else if($action == 'edit_subhobby'){
        $id = intval($_POST['id']);
        $new_name = trim($_POST['name']);
        if($id <= 0 || empty($new_name)){
            echo json_encode(['success' => false, 'error' => 'ข้อมูลไม่ครบ']);
            exit;
        }
        $new_name = $conn->real_escape_string($new_name);
        $sql = "UPDATE subhobby SET subhobby_name='$new_name' WHERE subhobby_id='$id'";
        if($conn->query($sql)){
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        exit;
    }

    // Job Category & Job Subcategory
    else if($action == 'add_job_category'){
        $job_category_name = trim($_POST['job_category_name']);
        if(empty($job_category_name)){
            echo json_encode(['success' => false, 'error' => 'ชื่อประเภทงานห้ามว่าง']);
            exit;
        }
        $job_category_name = $conn->real_escape_string($job_category_name);
        $sql = "INSERT INTO job_category (job_category_name) VALUES ('$job_category_name')";
        if($conn->query($sql)){
            echo json_encode(['success' => true, 'job_category_id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        exit;
    }
    else if($action == 'add_job_subcategory'){
        $job_subcategory_name = trim($_POST['job_subcategory_name']);
        $job_category_id = intval($_POST['job_category_id']);
        if(empty($job_subcategory_name) || $job_category_id <= 0){
            echo json_encode(['success' => false, 'error' => 'ข้อมูลไม่ครบ']);
            exit;
        }
        $job_subcategory_name = $conn->real_escape_string($job_subcategory_name);
        $sql = "INSERT INTO job_subcategory (job_subcategory_name, job_category_id) VALUES ('$job_subcategory_name', '$job_category_id')";
        if($conn->query($sql)){
            echo json_encode(['success' => true, 'job_subcategory_id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        exit;
    }
    else if($action == 'edit_job_category'){
        $id = intval($_POST['id']);
        $new_name = trim($_POST['name']);
        if($id <= 0 || empty($new_name)){
            echo json_encode(['success' => false, 'error' => 'ข้อมูลไม่ครบ']);
            exit;
        }
        $new_name = $conn->real_escape_string($new_name);
        $sql = "UPDATE job_category SET job_category_name='$new_name' WHERE job_category_id='$id'";
        if($conn->query($sql)){
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        exit;
    }
    else if($action == 'edit_job_subcategory'){
        $id = intval($_POST['id']);
        $new_name = trim($_POST['name']);
        if($id <= 0 || empty($new_name)){
            echo json_encode(['success' => false, 'error' => 'ข้อมูลไม่ครบ']);
            exit;
        }
        $new_name = $conn->real_escape_string($new_name);
        $sql = "UPDATE job_subcategory SET job_subcategory_name='$new_name' WHERE job_subcategory_id='$id'";
        if($conn->query($sql)){
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
?>