<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';  // Make sure this file sets up $db = new mysqli(...);

function handleError($message, $errorDetails = null) {
    $response = [
        "error" => $message
    ];
    
    if ($errorDetails !== null && !empty($errorDetails)) {
        $response["errorDetails"] = $errorDetails;
    }
    
    echo json_encode($response);
    exit;
}

// จับ PHP errors เพื่อป้องกันการแสดง HTML error
set_error_handler(function($severity, $message, $file, $line) {
    handleError("PHP Error: $message", [
        "file" => $file,
        "line" => $line
    ]);
});

try {
    // ตรวจสอบว่า config.php มีอยู่หรือไม่
    if (!file_exists('config.php')) {
        handleError("Config file not found");
    }
    
    require_once 'config.php';  // Make sure this file sets up $db = new mysqli(...);
    
    // ตรวจสอบการเชื่อมต่อฐานข้อมูล
    if ($db->connect_error) {
        handleError("Database connection failed: " . $db->connect_error);
    }


// เพิ่มฟังก์ชันสำหรับสร้าง WHERE clause จาก parameters
// function buildWhereClause($params) {
//     $where = [];
    
//     // General filters
//     if (!empty($params['startDate']) && !empty($params['endDate'])) {
//         $where[] = "pj.created_at BETWEEN '{$params['startDate']}' AND '{$params['endDate']}'";
//     } else {
//         if (!empty($params['startDate'])) {
//             $where[] = "pj.created_at >= '{$params['startDate']}'";
//         }
//         if (!empty($params['endDate'])) {
//             $where[] = "pj.created_at <= '{$params['endDate']}'";
//         }
//     }
    
//     if (!empty($params['start']) && !empty($params['end'])) {
//         $where[] = "pj.created_at BETWEEN '{$params['start']}' AND '{$params['end']}'";
//     } else {
//         if (!empty($params['start'])) {
//             $where[] = "pj.created_at >= '{$params['start']}'";
//         }
//         if (!empty($params['end'])) {
//             $where[] = "pj.created_at <= '{$params['end']}'";
//         }
//     }
    
//     // ตัวกรองหมวดหมู่งาน
//     if (!empty($params['category'])) {
//         $where[] = "pj.job_category_id = " . (int)$params['category'];
//     }
    
//     // ตัวกรองหมวดหมู่ย่อย (เพิ่มใหม่)
//     if (!empty($params['subcategory'])) {
//         $where[] = "pj.job_subcategory_id = " . (int)$params['subcategory'];
//     }
    
//     // ตัวกรองสถานะงาน
//     if (!empty($params['status'])) {
//         $where[] = "pj.job_status_id = " . (int)$params['status'];
//     }
    
//     // ตัวกรองประเภทรางวัล
//     if (!empty($params['reward'])) {
//         $where[] = "pj.reward_type_id = " . (int)$params['reward'];
//     }
    
//     // ตัวกรองอาจารย์
//     if (!empty($params['teacher'])) {
//         $teacher = addslashes($params['teacher']);
//         $where[] = "pj.teacher_id = '$teacher'";
//     }
    
//     // ตัวกรองเดือน
//     if (!empty($params['month'])) {
//         $month = (int)$params['month'];
//         $where[] = "MONTH(pj.created_at) = $month";
//     }
    
//     // ตัวกรองสาขา (major)
//     if (!empty($params['major'])) {
//         $major = (int)$params['major'];
//         // แก้ตาม DB structure ใหม่
//         if (strpos($params['endpoint'], 'student') !== false) {
//             $where[] = "s.major_id = $major";
//         } else if (strpos($params['endpoint'], 'professor') !== false) {
//             $where[] = "t.major_id = $major";
//         }
//     }
    
//     // ตัวกรองชั้นปี
//     if (!empty($params['year'])) {
//         $year = (int)$params['year'];
//         $where[] = "s.year = $year";
//     }
    
//     // ตัวกรองเพศ (เพิ่มใหม่)
//     if (!empty($params['gender'])) {
//         $gender = (int)$params['gender'];
//         if (strpos($params['endpoint'], 'student') !== false) {
//             $where[] = "s.gender_id = $gender";
//         } else if (strpos($params['endpoint'], 'professor') !== false) {
//             $where[] = "t.gender_id = $gender";
//         }
//     }
    
//     // ตัวกรองทักษะ (เพิ่มใหม่)
//     if (!empty($params['skill'])) {
//         $skill = (int)$params['skill'];
//         if (strpos($params['endpoint'], 'student') !== false) {
//             // สำหรับตาราง student_skill
//             $where[] = "EXISTS (SELECT 1 FROM student_skill ss WHERE ss.student_id = s.student_id AND ss.skill_id = $skill)";
//         } else {
//             // สำหรับตาราง post_job_skill
//             $where[] = "EXISTS (SELECT 1 FROM post_job_skill pjs WHERE pjs.post_job_id = pj.post_job_id AND pjs.skill_id = $skill)";
//         }
//     }
    
//     // ตัวกรองทักษะย่อย (เพิ่มใหม่)
//     if (!empty($params['subskill'])) {
//         $subskill = (int)$params['subskill'];
//         if (strpos($params['endpoint'], 'student') !== false) {
//             // สำหรับตาราง student_skill
//             $where[] = "EXISTS (SELECT 1 FROM student_skill ss WHERE ss.student_id = s.student_id AND ss.subskill_id = $subskill)";
//         } else {
//             // สำหรับตาราง post_job_skill
//             $where[] = "EXISTS (SELECT 1 FROM post_job_skill pjs WHERE pjs.post_job_id = pj.post_job_id AND pjs.subskill_id = $subskill)";
//         }
//     }
    
//     // ตัวกรองงานอดิเรก (เพิ่มใหม่)
//     if (!empty($params['hobby'])) {
//         $hobby = (int)$params['hobby'];
//         $where[] = "EXISTS (SELECT 1 FROM student_hobby sh WHERE sh.student_id = s.student_id AND sh.hobby_id = $hobby)";
//     }
    
//     // ตัวกรองงานอดิเรกย่อย (เพิ่มใหม่)
//     if (!empty($params['subhobby'])) {
//         $subhobby = (int)$params['subhobby'];
//         $where[] = "EXISTS (SELECT 1 FROM student_hobby sh WHERE sh.student_id = s.student_id AND sh.subhobby_id = $subhobby)";
//     }
    
//     return !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
// }

function buildWhereClause($params) {
    $where = [];
    
    // General filters
    if (!empty($params['startDate']) && !empty($params['endDate'])) {
        $where[] = "pj.created_at BETWEEN '{$params['startDate']}' AND '{$params['endDate']}'";
    } else {
        if (!empty($params['startDate'])) {
            $where[] = "pj.created_at >= '{$params['startDate']}'";
        }
        if (!empty($params['endDate'])) {
            $where[] = "pj.created_at <= '{$params['endDate']}'";
        }
    }
    
    if (!empty($params['start']) && !empty($params['end'])) {
        $where[] = "pj.created_at BETWEEN '{$params['start']}' AND '{$params['end']}'";
    } else {
        if (!empty($params['start'])) {
            $where[] = "pj.created_at >= '{$params['start']}'";
        }
        if (!empty($params['end'])) {
            $where[] = "pj.created_at <= '{$params['end']}'";
        }
    }
    
    // ตัวกรองหมวดหมู่งาน
    if (!empty($params['category'])) {
        $where[] = "pj.job_category_id = " . (int)$params['category'];
    }
    
    // ตัวกรองหมวดหมู่ย่อย (เพิ่มใหม่)
    if (!empty($params['subcategory'])) {
        $where[] = "pj.job_subcategory_id = " . (int)$params['subcategory'];
    }
    
    // ตัวกรองสถานะงาน
    if (!empty($params['status'])) {
        $where[] = "pj.job_status_id = " . (int)$params['status'];
    }
    
    // ตัวกรองประเภทรางวัล
    if (!empty($params['reward'])) {
        $where[] = "pj.reward_type_id = " . (int)$params['reward'];
    }
    
    // ตัวกรองอาจารย์
    if (!empty($params['teacher'])) {
        $teacher = addslashes($params['teacher']);
        $where[] = "pj.teacher_id = '$teacher'";
    }
    
    // ตัวกรองเดือน
    if (!empty($params['month'])) {
        $month = (int)$params['month'];
        $where[] = "MONTH(pj.created_at) = $month";
    }
    
    // ตัวกรองสาขา (major)
    if (!empty($params['major'])) {
        $major = (int)$params['major'];
        // แก้ตาม DB structure ใหม่
        if (strpos($params['endpoint'], 'student') !== false) {
            $where[] = "s.major_id = $major";
        } else if (strpos($params['endpoint'], 'professor') !== false) {
            $where[] = "t.major_id = $major";
        }
    }
    
    // ตัวกรองชั้นปี
    if (!empty($params['year'])) {
        $year = (int)$params['year'];
        $where[] = "s.year = $year";
    }
    
    // ตัวกรองเพศ
    if (!empty($params['gender'])) {
        $gender = (int)$params['gender'];
        if (strpos($params['endpoint'], 'student') !== false) {
            $where[] = "s.gender_id = $gender";
        } else if (strpos($params['endpoint'], 'professor') !== false) {
            $where[] = "t.gender_id = $gender";
        }
    }
    
    // ตัวกรองทักษะ
    if (!empty($params['skill'])) {
        $skill = (int)$params['skill'];
        if (strpos($params['endpoint'], 'student') !== false) {
            // สำหรับตาราง student_skill
            $where[] = "EXISTS (SELECT 1 FROM student_skill ss WHERE ss.student_id = s.student_id AND ss.skill_id = $skill)";
        } else {
            // สำหรับตาราง post_job_skill
            $where[] = "EXISTS (SELECT 1 FROM post_job_skill pjs WHERE pjs.post_job_id = pj.post_job_id AND pjs.skill_id = $skill)";
        }
    }
    
    // ตัวกรองทักษะย่อย
    if (!empty($params['subskill'])) {
        $subskill = (int)$params['subskill'];
        if (strpos($params['endpoint'], 'student') !== false) {
            // สำหรับตาราง student_skill
            $where[] = "EXISTS (SELECT 1 FROM student_skill ss WHERE ss.student_id = s.student_id AND ss.subskill_id = $subskill)";
        } else {
            // สำหรับตาราง post_job_skill
            $where[] = "EXISTS (SELECT 1 FROM post_job_skill pjs WHERE pjs.post_job_id = pj.post_job_id AND pjs.subskill_id = $subskill)";
        }
    }
    
    // ตัวกรองงานอดิเรก
    if (!empty($params['hobby'])) {
        $hobby = (int)$params['hobby'];
        $where[] = "EXISTS (SELECT 1 FROM student_hobby sh WHERE sh.student_id = s.student_id AND sh.hobby_id = $hobby)";
    }
    
    // ตัวกรองงานอดิเรกย่อย
    if (!empty($params['subhobby'])) {
        $subhobby = (int)$params['subhobby'];
        $where[] = "EXISTS (SELECT 1 FROM student_hobby sh WHERE sh.student_id = s.student_id AND sh.subhobby_id = $subhobby)";
    }
    
    // ตัวกรองมุมมอง (viewType) - เพิ่มใหม่
    if (!empty($params['viewType'])) {
        // อาจจำเป็นต้องใช้ในบางกรณี แต่ส่วนใหญ่จะจัดการแยกใน endpoint เช่น jobs-over-time
    }
    
    return !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
}

// ดึงค่า parameters จาก request
$params = [
    'startDate' => $_GET['startDate'] ?? $_GET['start'] ?? '',
    'endDate' => $_GET['endDate'] ?? $_GET['end'] ?? '',
    'start' => $_GET['start'] ?? $_GET['startDate'] ?? '',
    'end' => $_GET['end'] ?? $_GET['endDate'] ?? '',
    'category' => $_GET['category'] ?? '',
    'subcategory' => $_GET['subcategory'] ?? '', // เพิ่มใหม่
    'status' => $_GET['status'] ?? '',
    'reward' => $_GET['reward'] ?? '',
    'teacher' => $_GET['teacher'] ?? '',
    'month' => $_GET['month'] ?? '',
    'major' => $_GET['major'] ?? '',
    'year' => $_GET['year'] ?? '',
    'semester' => $_GET['semester'] ?? '',
    'limit' => $_GET['limit'] ?? '',
    'gender' => $_GET['gender'] ?? '', // เพิ่มใหม่
    'skill' => $_GET['skill'] ?? '', // เพิ่มใหม่
    'subskill' => $_GET['subskill'] ?? '', // เพิ่มใหม่
    'hobby' => $_GET['hobby'] ?? '', // เพิ่มใหม่
    'subhobby' => $_GET['subhobby'] ?? '', // เพิ่มใหม่
    'endpoint' => $_GET['endpoint'] ?? ''
];

$endpoint = $_GET['endpoint'] ?? '';
$whereClause = buildWhereClause($params);

$sql = '';
$data = [];

switch ($endpoint) {
    case 'job-market':
        $sql = "
            SELECT jc.job_category_name AS category_name,
                   COUNT(pj.post_job_id) AS total_jobs
            FROM job_category jc
            LEFT JOIN post_job pj ON jc.job_category_id = pj.job_category_id
            $whereClause
            GROUP BY jc.job_category_id, jc.job_category_name
            ORDER BY total_jobs DESC
        ";
        break;

    case 'applications':
        $sql = "
            SELECT p.title,
                   COUNT(a.job_application_id) AS total_applications
            FROM job_application a
            LEFT JOIN post_job p ON a.post_job_id = p.post_job_id
            $whereClause
            GROUP BY p.post_job_id, p.title
        ";
        
        // เพิ่มการรองรับ limit
        if (!empty($params['limit']) && (int)$params['limit'] > 0) {
            $sql .= " ORDER BY total_applications DESC LIMIT " . (int)$params['limit'];
        }
        break;

        case 'job-categories-stats':
            $sql = "
                SELECT 
                    jc.job_category_name,
                    COUNT(pj.post_job_id) AS total_jobs,
                    ROUND(
                      COUNT(pj.post_job_id) * 100.0 
                      / NULLIF(
                          (SELECT COUNT(*) FROM post_job WHERE job_status_id != 3),
                          0
                      ),
                      1
                    ) AS percentage
                FROM job_category jc
                LEFT JOIN post_job pj
                       ON jc.job_category_id = pj.job_category_id
                       AND pj.job_status_id != 3
            ";
            if (!empty($whereClause)) {
                $sql .= preg_replace('/^WHERE/i', 'WHERE pj.job_status_id != 3 AND', $whereClause, 1);
            } else {
                $sql .= " WHERE pj.job_status_id != 3";
            }
            $sql .= "
                GROUP BY jc.job_category_id, jc.job_category_name
                ORDER BY total_jobs DESC
            ";
            break;

            case 'job-status-stats':
                $sql = "
                    SELECT 
                        js.job_status_name,
                        COUNT(pj.post_job_id) AS total_jobs,
                        ROUND(
                            COUNT(pj.post_job_id) * 100.0 
                            / (SELECT COUNT(*) FROM post_job),
                            1
                        ) AS percentage
                    FROM job_status js
                    LEFT JOIN post_job pj ON js.job_status_id = pj.job_status_id
                    $whereClause
                    GROUP BY js.job_status_id, js.job_status_name
                    ORDER BY js.job_status_id
                ";
                break;
        
    case 'applications-over-time':
        $sql = "
            SELECT 
                DATE_FORMAT(ja.created_at, '%Y-%m') AS month,
                COUNT(ja.job_application_id) AS total_applications
            FROM job_application ja
            LEFT JOIN post_job pj ON ja.post_job_id = pj.post_job_id
            $whereClause
            GROUP BY DATE_FORMAT(ja.created_at, '%Y-%m')
            ORDER BY month
        ";
        break;

    case 'major-distribution':
        $sql = "
            SELECT 
                m.major_name,
                COUNT(s.student_id) AS total_students
            FROM student s
            LEFT JOIN major m ON s.major_id = m.major_id
            LEFT JOIN job_application ja ON s.student_id = ja.student_id
            LEFT JOIN post_job pj ON ja.post_job_id = pj.post_job_id
            $whereClause
            GROUP BY m.major_id, m.major_name
            ORDER BY total_students DESC
        ";
        break;

    // Find the avg-gpa case in the switch statement and update it:
case 'avg-gpa':
    $sql = "
        SELECT 
            AVG(ja.GPA) AS avg_gpa
        FROM job_application ja
        LEFT JOIN post_job pj ON ja.post_job_id = pj.post_job_id
        LEFT JOIN student s ON ja.student_id = s.student_id  
    ";
    
    // Add WHERE clause
    if (!empty($whereClause)) {
        $sql .= " $whereClause";
    } else {
        $additionalWhere = [];
        
        // Handle specific filters
        if (!empty($params['major'])) {
            $additionalWhere[] = "s.major_id = " . (int)$params['major'];
        }
        
        if (!empty($params['year'])) {
            $additionalWhere[] = "s.year = " . (int)$params['year'];
        }
        
        if (!empty($additionalWhere)) {
            $sql .= " WHERE " . implode(" AND ", $additionalWhere);
        }
    }
    break;

    case 'top5-completed':
        $sql = "
            SELECT 
                s.stu_name AS name,
                COUNT(aa.accepted_application_id) AS completed_count
            FROM accepted_application aa
            JOIN student s ON aa.student_id = s.student_id
            LEFT JOIN post_job pj ON aa.post_job_id = pj.post_job_id
            WHERE aa.accept_status_id = 1
        ";

        if (!empty($whereClause)) {
            $sql .= preg_replace('/^WHERE/i', 'AND', $whereClause, 1);
        }

        $sql .= "
            GROUP BY s.student_id, s.stu_name
            ORDER BY completed_count DESC
        ";
        
        // Add limit if specified, otherwise default to 5
        if (!empty($params['limit']) && (int)$params['limit'] > 0) {
            $sql .= " LIMIT " . (int)$params['limit'];
        } else {
            $sql .= " LIMIT 5";
        }
        break;

        case 'reward-type-stats':
            $sql = "
                SELECT
                    rt.reward_type_name,
                    COUNT(pj.post_job_id) AS total_jobs,
                    ROUND(
                        COUNT(pj.post_job_id) * 100.0 
                        / NULLIF(
                            (SELECT COUNT(*) FROM post_job WHERE job_status_id != 3),
                            0
                        ),
                        1
                    ) AS percentage
                FROM reward_type rt
                LEFT JOIN post_job pj
                       ON rt.reward_type_id = pj.reward_type_id
                       AND pj.job_status_id != 3
            ";
            if (!empty($whereClause)) {
                $sql .= preg_replace('/^WHERE/i','WHERE pj.job_status_id != 3 AND',$whereClause,1);
            } else {
                $sql .= " WHERE pj.job_status_id != 3";
            }
            $sql .= "
                GROUP BY rt.reward_type_id, rt.reward_type_name
                ORDER BY rt.reward_type_id
            ";
            break;

            case 'pay-rate':
                $sql = "
                    SELECT
                        COUNT(*) AS total_jobs,
                        ROUND(
                            COUNT(CASE WHEN pj.reward_type_id = 2 OR pj.reward_type_id = 3 THEN 1 END) * 100.0 
                            / COUNT(*),
                            2
                        ) AS money_percentage,
                        ROUND(
                            COUNT(CASE WHEN pj.reward_type_id = 1 THEN 1 END) * 100.0 
                            / COUNT(*),
                            2
                        ) AS experience_percentage
                    FROM post_job pj
                    WHERE pj.job_status_id != 3
                ";
                if (!empty($whereClause)) {
                    $trimmed = preg_replace('/^WHERE\s+/i', '', $whereClause);
                    $sql .= " AND $trimmed";
                }
                break;

                case 'completion-rate':
                    $sql = "
                        SELECT 
                            COUNT(DISTINCT ja.job_application_id) AS total_applications,
                            COUNT(DISTINCT CASE WHEN aa.accept_status_id = 1 THEN aa.accepted_application_id END) AS accepted_applications,
                            ROUND(
                                COUNT(DISTINCT CASE WHEN aa.accept_status_id = 1 THEN aa.accepted_application_id END) * 100.0
                                / NULLIF(COUNT(DISTINCT ja.job_application_id), 0),
                                2
                            ) AS completion_percentage
                        FROM job_application ja
                        LEFT JOIN accepted_application aa ON ja.job_application_id = aa.job_application_id
                        LEFT JOIN post_job pj ON ja.post_job_id = pj.post_job_id
                        $whereClause
                    ";
                    break;

                    case 'application-status-distribution':
                        $sql = "
                            SELECT 
                                COUNT(DISTINCT CASE WHEN aa.accept_status_id = 1 THEN aa.accepted_application_id END) AS accepted_applications,
                                COUNT(DISTINCT CASE WHEN aa.accept_status_id = 2 THEN aa.accepted_application_id END) AS rejected_applications,
                                COUNT(DISTINCT CASE WHEN aa.accept_status_id = 3 OR aa.accept_status_id IS NULL THEN ja.job_application_id END) AS pending_applications
                            FROM job_application ja
                            LEFT JOIN accepted_application aa ON ja.job_application_id = aa.job_application_id
                            LEFT JOIN post_job pj ON ja.post_job_id = pj.post_job_id
                            $whereClause
                        ";
                        break;
        
    case 'active-students':
        // ปรับปรุง query active-students เพื่อรองรับ filter เพิ่มเติม
        $sql = "
          SELECT 
             COUNT(DISTINCT s.student_id) AS total_students,
             COUNT(DISTINCT CASE WHEN aa.accept_status_id = 1 THEN s.student_id END) AS accepted_students,
             COUNT(DISTINCT s.student_id) - COUNT(DISTINCT CASE WHEN aa.accept_status_id = 1 THEN s.student_id END) AS not_accepted_students
          FROM student s
          LEFT JOIN job_application ja ON s.student_id = ja.student_id
          LEFT JOIN post_job pj ON ja.post_job_id = pj.post_job_id
          LEFT JOIN accepted_application aa ON s.student_id = aa.student_id AND aa.accept_status_id = 1
        ";
        
        // เพิ่มเงื่อนไข WHERE จาก filters
        if (!empty($whereClause)) {
            $sql .= " $whereClause";
        }
        
        // เพิ่มเงื่อนไขเฉพาะสำหรับ major และ year
        if (empty($whereClause)) {
            $additionalWhere = [];
            
            if (!empty($params['major'])) {
                $additionalWhere[] = "s.major_id = " . (int)$params['major'];
            }
            
            if (!empty($params['year'])) {
                $additionalWhere[] = "s.year = " . (int)$params['year'];
            }
            
            if (!empty($additionalWhere)) {
                $sql .= " WHERE " . implode(" AND ", $additionalWhere);
            }
        } else {
            // ถ้ามี WHERE clause อยู่แล้ว ให้เพิ่ม AND
            if (!empty($params['major'])) {
                $sql .= " AND s.major_id = " . (int)$params['major'];
            }
            
            if (!empty($params['year'])) {
                $sql .= " AND s.year = " . (int)$params['year'];
            }
        }
        break;

    case 'student-performance':
        $sql = "
            SELECT s.stu_name AS name,
                   AVG(r.rating) AS avg_rating
            FROM student s
            LEFT JOIN review r ON s.student_id = r.student_id
            LEFT JOIN post_job pj ON r.post_job_id = pj.post_job_id
            $whereClause
            GROUP BY s.student_id, s.stu_name
            ORDER BY avg_rating DESC
        ";
        break;

    case 'active-professors':
        $sql = "
            SELECT 
                (SELECT COUNT(*) FROM teacher) AS total_professors,
                COUNT(DISTINCT t.teacher_id) AS active_professors
            FROM teacher t
            JOIN post_job pj ON t.teacher_id = pj.teacher_id
            $whereClause
        ";
        break;

    case 'top-professors':
        // สร้าง base query
        $sql = "
            SELECT t.teach_name AS name,
                   COUNT(pj.post_job_id) AS job_count";

        // เพิ่มเฟิลด์กรณีมีการเลือก activityType
        if (isset($_GET['activityType']) && $_GET['activityType'] == 'success' || $_GET['activityType'] == 'rate') {
            $sql .= ",
                   COUNT(CASE WHEN pj.job_status_id = 2 THEN pj.post_job_id END) AS success_count";
        }
            
        $sql .= "
            FROM teacher t
            JOIN post_job pj ON t.teacher_id = pj.teacher_id
        ";
            
        // เริ่มต้น WHERE clause
        $whereConditions = [];
            
        // เพิ่มเงื่อนไขกรองตาม major
        if (!empty($_GET['major'])) {
            $major = (int)$_GET['major'];
            $whereConditions[] = "t.major_id = $major";
        }

        // เพิ่มเงื่อนไขกรองตาม gender (เพิ่มใหม่)
        if (!empty($_GET['gender'])) {
            $gender = (int)$_GET['gender'];
            $whereConditions[] = "t.gender_id = $gender";
        }
            
        // เพิ่มเงื่อนไขกรองตาม category
        if (!empty($_GET['category'])) {
            $category = (int)$_GET['category'];
            $whereConditions[] = "pj.job_category_id = $category";
        }

        // เพิ่มเงื่อนไขกรองตาม subcategory (เพิ่มใหม่)
        if (!empty($_GET['subcategory'])) {
            $subcategory = (int)$_GET['subcategory'];
            $whereConditions[] = "pj.job_subcategory_id = $subcategory";
        }
            
        // เพิ่มเงื่อนไขกรองตามวันที่
        if (!empty($_GET['start']) && !empty($_GET['end'])) {
            $start = $_GET['start'];
            $end = $_GET['end'];
            $whereConditions[] = "pj.created_at BETWEEN '$start' AND '$end'";
        } else {
            if (!empty($_GET['start'])) {
                $start = $_GET['start'];
                $whereConditions[] = "pj.created_at >= '$start'";
            }
            if (!empty($_GET['end'])) {
                $end = $_GET['end'];
                $whereConditions[] = "pj.created_at <= '$end'";
            }
        }
            
        // รวม WHERE conditions ถ้ามี
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }
            
        // สรุป query
        $sql .= "
            GROUP BY t.teacher_id, t.teach_name
        ";

        // กำหนด ORDER BY ตาม activityType ที่เลือก
        if (isset($_GET['activityType']) && $_GET['activityType'] == 'success') {
            $sql .= " ORDER BY success_count DESC";
        } else if (isset($_GET['activityType']) && $_GET['activityType'] == 'rate') {
            $sql .= " ORDER BY (CASE WHEN job_count > 0 THEN success_count / job_count ELSE 0 END) DESC";
        } else {
            $sql .= " ORDER BY job_count DESC";
        }
            
        // เพิ่ม limit ถ้ามีการกำหนด
        if (!empty($_GET['limit']) && (int)$_GET['limit'] > 0) {
            $sql .= " LIMIT " . (int)$_GET['limit'];
        } else {
            $sql .= " LIMIT 5"; // ค่าเริ่มต้นคือ 5
        }
        break;

    case 'job-posts':
        $sql = "
            SELECT jc.job_category_name, COUNT(pj.post_job_id) AS total_jobs
            FROM job_category jc
            LEFT JOIN post_job pj ON jc.job_category_id = pj.job_category_id
            $whereClause
            GROUP BY jc.job_category_name
        ";
        break;

    // ตำแหน่ง: ในไฟล์ api.php ในส่วนของ case 'top-jobs':

    case 'top-jobs':
        // สร้าง base query
        $sql = "
            SELECT p.title,
                   COUNT(a.job_application_id) AS total_applications
            FROM post_job p
            LEFT JOIN job_application a ON p.post_job_id = a.post_job_id
        ";
        
        // เริ่มต้น WHERE clause
        $whereConditions = [];
        
        // เพิ่มเงื่อนไขกรองตามหมวดหมู่งาน
        if (!empty($_GET['category'])) {
            $category = (int)$_GET['category'];
            $whereConditions[] = "p.job_category_id = $category";
        }
        
        // เพิ่มเงื่อนไขกรองตามหมวดหมู่ย่อย (เพิ่มใหม่)
        if (!empty($_GET['subcategory'])) {
            $subcategory = (int)$_GET['subcategory'];
            $whereConditions[] = "p.job_subcategory_id = $subcategory";
        }
        
        // เพิ่มเงื่อนไขกรองตามสถานะงาน
        if (!empty($_GET['status'])) {
            $status = (int)$_GET['status'];
            $whereConditions[] = "p.job_status_id = $status";
        }
        
        // เพิ่มเงื่อนไขกรองตามช่วงเวลา
        if (!empty($_GET['start']) && !empty($_GET['end'])) {
            $start = $_GET['start'];
            $end = $_GET['end'];
            $whereConditions[] = "p.created_at BETWEEN '$start' AND '$end'";
        } else {
            if (!empty($_GET['start'])) {
                $start = $_GET['start'];
                $whereConditions[] = "p.created_at >= '$start'";
            }
            if (!empty($_GET['end'])) {
                $end = $_GET['end'];
                $whereConditions[] = "p.created_at <= '$end'";
            }
        }
        
        // เพิ่มเงื่อนไขกรองตามประเภทผลตอบแทน
        if (!empty($_GET['reward'])) {
            $reward = (int)$_GET['reward'];
            $whereConditions[] = "p.reward_type_id = $reward";
        }
        
        // เพิ่มเงื่อนไขกรองตามอาจารย์
        if (!empty($_GET['teacher'])) {
            $teacher = $_GET['teacher'];
            $whereConditions[] = "p.teacher_id = '$teacher'";
        }
        
        // เพิ่มเงื่อนไขกรองตามทักษะ (เพิ่มใหม่)
        if (!empty($_GET['skill'])) {
            $skill = (int)$_GET['skill'];
            $whereConditions[] = "EXISTS (SELECT 1 FROM post_job_skill pjs WHERE pjs.post_job_id = p.post_job_id AND pjs.skill_id = $skill)";
        }
        
        // เพิ่มเงื่อนไขกรองตามทักษะย่อย (เพิ่มใหม่)
        if (!empty($_GET['subskill'])) {
            $subskill = (int)$_GET['subskill'];
            $whereConditions[] = "EXISTS (SELECT 1 FROM post_job_skill pjs WHERE pjs.post_job_id = p.post_job_id AND pjs.subskill_id = $subskill)";
        }
        
        // รวม WHERE conditions ถ้ามี
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        // สรุป query
        $sql .= "
            GROUP BY p.post_job_id, p.title
        ";
        
        // เลือกวิธีการเรียงลำดับข้อมูล
        if (!empty($_GET['sortBy']) && $_GET['sortBy'] == 'duration') {
            $sql .= " ORDER BY DATEDIFF(p.job_end, p.job_start) DESC";
        } else {
            $sql .= " ORDER BY total_applications DESC";
        }
        
        // เพิ่ม limit ถ้ามีการกำหนด
        if (!empty($_GET['limit']) && (int)$_GET['limit'] > 0) {
            $sql .= " LIMIT " . (int)$_GET['limit'];
        } else {
            $sql .= " LIMIT 5"; // ค่าเริ่มต้น
        }
        break;

        case 'jobs-over-time':
            // ตรวจสอบว่ามีการเลือก View Type (monthly, yearly) หรือไม่
            $viewType = $_GET['viewType'] ?? 'monthly';
        
            // ---- 1) เตรียม whereClause สำหรับ WHERE
            $whereClause = "WHERE 1=1";
        
            // (a) กำหนดช่วงวันแบบ semester+year หรือ start/end
            if (!empty($_GET['semester']) && !empty($_GET['year'])) {
                $semester = $_GET['semester'];
                $year = (int)$_GET['year'];
        
                $dateRange = [];
                switch ($semester) {
                    case 'first':
                        $dateRange = [
                            'start' => "$year-06-25",
                            'end'   => "$year-10-25"
                        ];
                        break;
                    case 'second':
                        $dateRange = [
                            'start' => "$year-11-25",
                            'end'   => ($year+1)."-03-25"
                        ];
                        break;
                    case 'summer':
                        $dateRange = [
                            'start' => "$year-03-30",
                            'end'   => "$year-06-01"
                        ];
                        break;
                }
                if (!empty($dateRange)) {
                    $whereClause .= " 
                        AND pj.created_at BETWEEN '{$dateRange['start']}' AND '{$dateRange['end']}'";
                }
            }
            else if (!empty($_GET['start']) && !empty($_GET['end'])) {
                $start = $_GET['start'];
                $end   = $_GET['end'];
                $whereClause .= " 
                    AND pj.created_at BETWEEN '{$start}' AND '{$end}'";
            }
        
            // (b) กรอง category, status
            if (!empty($_GET['category'])) {
                $cat = (int)$_GET['category'];
                $whereClause .= " AND pj.job_category_id = $cat";
            }
            
            // กรอง subcategory (เพิ่มใหม่)
            if (!empty($_GET['subcategory'])) {
                $subcat = (int)$_GET['subcategory'];
                $whereClause .= " AND pj.job_subcategory_id = $subcat";
            }
            
            if (!empty($_GET['status'])) {
                $stat = (int)$_GET['status'];
                $whereClause .= " AND pj.job_status_id = $stat";
            }
        
            // ---- 2) เลือก groupBy (month หรือ year)
            $dateField   = "DATE_FORMAT(pj.created_at, '%Y-%m') AS month";
            $groupByPart = "GROUP BY DATE_FORMAT(pj.created_at, '%Y-%m')";
            $orderByPart = "ORDER BY month";
        
            if ($viewType === 'yearly') {
                $dateField   = "YEAR(pj.created_at) AS year_label";
                $groupByPart = "GROUP BY YEAR(pj.created_at)";
                $orderByPart = "ORDER BY year_label";
            }
        
            // ---- 3) ประกอบคำสั่ง SQL สุดท้าย
            $sql = "
                SELECT 
                    $dateField,
                    COUNT(pj.post_job_id) AS total_posts
                FROM post_job pj
                $whereClause
                $groupByPart
                $orderByPart
            ";
        
            error_log("DEBUG - jobs-over-time SQL: " . $sql);
        
            $result = $db->query($sql);
            if (!$result) {
                handleError("Database query failed: " . $db->error, ["sql" => $sql]);
            }
        
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        
            echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
            break;
    
            case 'jobs-taken-overtime':
                $viewType = $_GET['viewType'] ?? 'monthly';
            
                // เฉพาะงานที่ปิดแล้ว job_status=2
                $whereClause = "WHERE pj.job_status_id = 2";
            
                // (a) ช่วงวัน
                if (!empty($_GET['semester']) && !empty($_GET['year'])) {
                    $semester = $_GET['semester'];
                    $year = (int)$_GET['year'];
                    $dateRange = [];
                    switch ($semester) {
                        case 'first':
                            $dateRange = [
                                'start' => "$year-06-25",
                                'end'   => "$year-10-25"
                            ];
                            break;
                        case 'second':
                            $dateRange = [
                                'start' => "$year-11-25",
                                'end'   => ($year+1)."-03-25"
                            ];
                            break;
                        case 'summer':
                            $dateRange = [
                                'start' => "$year-03-30",
                                'end'   => "$year-06-01"
                            ];
                            break;
                    }
                    if (!empty($dateRange)) {
                        $whereClause .= " 
                            AND pj.job_end BETWEEN '{$dateRange['start']}' AND '{$dateRange['end']}'";
                    }
                }
                else if (!empty($_GET['start']) && !empty($_GET['end'])) {
                    $start = $_GET['start'];
                    $end   = $_GET['end'];
                    $whereClause .= " 
                        AND pj.job_end BETWEEN '{$start}' AND '{$end}'";
                }
            
                // (b) กรอง category
                if (!empty($_GET['category'])) {
                    $cat = (int)$_GET['category'];
                    $whereClause .= " AND pj.job_category_id = $cat";
                }
                
                // กรอง subcategory (เพิ่มใหม่)
                if (!empty($_GET['subcategory'])) {
                    $subcat = (int)$_GET['subcategory'];
                    $whereClause .= " AND pj.job_subcategory_id = $subcat";
                }
            
                // (c) groupBy
                $dateField   = "DATE_FORMAT(pj.job_end, '%Y-%m') AS month";
                $groupByPart = "GROUP BY DATE_FORMAT(pj.job_end, '%Y-%m')";
                $orderByPart = "ORDER BY month";
            
                if ($viewType === 'yearly') {
                    $dateField   = "YEAR(pj.job_end) AS year_label";
                    $groupByPart = "GROUP BY YEAR(pj.job_end)";
                    $orderByPart = "ORDER BY year_label";
                }
            
                // ---- 3) ประกอบคำสั่ง SQL สุดท้าย
                $sql = "
                    SELECT
                        $dateField,
                        COUNT(pj.post_job_id) AS total_jobs_closed
                    FROM post_job pj
                    $whereClause
                    $groupByPart
                    $orderByPart
                ";
            
                error_log("DEBUG - jobs-taken-overtime SQL: " . $sql);
            
                $result = $db->query($sql);
                if (!$result) {
                    handleError("Database query failed: " . $db->error, ["sql" => $sql]);
                }
            
                $data = [];
                while ($row = $result->fetch_assoc()) {
                    $data[] = $row;
                }
            
                echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                exit;
                break;
    
        case 'job-subcategories-stats':
            $sql = "
                SELECT jsc.job_subcategory_name AS subcategories_name,
                       COUNT(pj.post_job_id) AS total_jobs,
                       ROUND(
                          COUNT(pj.post_job_id) * 100.0 
                          / NULLIF(
                              (SELECT COUNT(*) FROM post_job WHERE job_status_id != 3),
                              0
                          ),
                          1
                        ) AS percentage
                FROM job_subcategory jsc
                LEFT JOIN post_job pj ON jsc.job_subcategory_id = pj.job_subcategory_id
            ";
            if (!empty($whereClause)) {
                $sql .= " " . $whereClause;
            } else {
                $sql .= " WHERE pj.job_status_id != 3";
            }
            $sql .= "
                GROUP BY jsc.job_subcategory_id, jsc.job_subcategory_name
                ORDER BY total_jobs DESC
            ";
            break;

    case 'applications-per-student':
        $sql = "
            SELECT 
                s.stu_name AS name,
                COUNT(ja.job_application_id) AS total_apps
            FROM student s
            JOIN job_application ja 
                ON s.student_id = ja.student_id
            JOIN post_job pj
                ON ja.post_job_id = pj.post_job_id
            $whereClause
            GROUP BY s.student_id, s.stu_name
            ORDER BY total_apps DESC
        ";
        break;

    // FILTER ENDPOINTS
    case 'categories-list':
        $sql = "SELECT job_category_id, job_category_name FROM job_category ORDER BY job_category_name";
        $result = $db->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        exit;

    case 'status-list':
        $sql = "SELECT job_status_id, job_status_name FROM job_status ORDER BY job_status_id";
        $result = $db->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        exit;

    case 'reward-list':
        $sql = "SELECT reward_type_id, reward_type_name FROM reward_type ORDER BY reward_type_id";
        $result = $db->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        exit;
        
    case 'teacher-list':
        $sql = "SELECT teacher_id, teach_name FROM teacher ORDER BY teach_name";
        $result = $db->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        exit;

    case 'skill-list':
        $sql = "SELECT skill_id, skill_name FROM skill ORDER BY skill_id";
        $result = $db->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        exit;
        
    // เพิ่ม endpoint ใหม่สำหรับ filter
    case 'major-list':
        $sql = "SELECT major_id, major_name FROM major ORDER BY major_name";
        $result = $db->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        exit;
        
    case 'year-list':
        // สร้าง list ของปีการศึกษาที่มีนิสิต
        $sql = "SELECT DISTINCT year FROM student ORDER BY year";
        $result = $db->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        exit;
        
    // เพิ่ม endpoint ใหม่สำหรับข้อมูลเพศ
    case 'gender-list':
        $sql = "SELECT gender_id, gender_name FROM gender ORDER BY gender_id";
        $result = $db->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        exit;
        
    // เพิ่ม endpoint ใหม่สำหรับหมวดหมู่ย่อย
    case 'subcategory-list':
        $sql = "SELECT job_subcategory_id, job_subcategory_name, job_category_id FROM job_subcategory";
        if (!empty($_GET['category'])) {
            $category = (int)$_GET['category'];
            $sql .= " WHERE job_category_id = $category";
        }
        $sql .= " ORDER BY job_subcategory_name";
        $result = $db->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        exit;
        
    // เพิ่ม endpoint ใหม่สำหรับทักษะย่อย
    case 'subskill-list':
        $sql = "SELECT subskill_id, subskill_name, skill_id FROM subskill";
        if (!empty($_GET['skill'])) {
            $skill = (int)$_GET['skill'];
            $sql .= " WHERE skill_id = $skill";
        }
        $sql .= " ORDER BY subskill_name";
        $result = $db->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        exit;
        
    // เพิ่ม endpoint ใหม่สำหรับงานอดิเรก
    case 'hobby-list':
        $sql = "SELECT hobby_id, hobby_name FROM hobby ORDER BY hobby_name";
        $result = $db->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        exit;
        
    // เพิ่ม endpoint ใหม่สำหรับงานอดิเรกย่อย
    case 'subhobby-list':
        $sql = "SELECT subhobby_id, subhobby_name, hobby_id FROM subhobby";
        if (!empty($_GET['hobby'])) {
            $hobby = (int)$_GET['hobby'];
            $sql .= " WHERE hobby_id = $hobby";
        }
        $sql .= " ORDER BY subhobby_name";
        $result = $db->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        exit;

    case 'top5-students':
        // อ่าน sortBy ว่าเป็น rating หรือ accept
        $sort = $_GET['sort'] ?? 'rating';
        
        if ($sort === 'accept') {
            // เรียงตาม accept_count
            $sql = "
                SELECT s.stu_name AS name,
                       COUNT(aa.accepted_application_id) AS accept_count
                FROM student s
                LEFT JOIN accepted_application aa ON s.student_id = aa.student_id
                WHERE aa.accept_status_id = 1
            ";
            
            // เพิ่มเงื่อนไข major
            if (!empty($_GET['major'])) {
                $major = (int)$_GET['major'];
                $sql .= " AND s.major_id = $major";
            }
            
            // เพิ่มเงื่อนไข year
            if (!empty($_GET['year'])) {
                $year = (int)$_GET['year'];
                $sql .= " AND s.year = $year";
            }
            
            // เพิ่มเงื่อนไข gender (เพิ่มใหม่)
            if (!empty($_GET['gender'])) {
                $gender = (int)$_GET['gender'];
                $sql .= " AND s.gender_id = $gender";
            }
            
            // เพิ่มเงื่อนไข skill (เพิ่มใหม่)
            if (!empty($_GET['skill'])) {
                $skill = (int)$_GET['skill'];
                $sql .= " AND EXISTS (SELECT 1 FROM student_skill ss WHERE ss.student_id = s.student_id AND ss.skill_id = $skill)";
            }
            
            // เพิ่มเงื่อนไข subskill (เพิ่มใหม่)
            if (!empty($_GET['subskill'])) {
                $subskill = (int)$_GET['subskill'];
                $sql .= " AND EXISTS (SELECT 1 FROM student_skill ss WHERE ss.student_id = s.student_id AND ss.subskill_id = $subskill)";
            }
            
            // เพิ่มเงื่อนไข hobby (เพิ่มใหม่)
            if (!empty($_GET['hobby'])) {
                $hobby = (int)$_GET['hobby'];
                $sql .= " AND EXISTS (SELECT 1 FROM student_hobby sh WHERE sh.student_id = s.student_id AND sh.hobby_id = $hobby)";
            }
            
            // เพิ่มเงื่อนไข subhobby (เพิ่มใหม่)
            if (!empty($_GET['subhobby'])) {
                $subhobby = (int)$_GET['subhobby'];
                $sql .= " AND EXISTS (SELECT 1 FROM student_hobby sh WHERE sh.student_id = s.student_id AND sh.subhobby_id = $subhobby)";
            }
            
            $sql .= "
                GROUP BY s.student_id, s.stu_name
                ORDER BY accept_count DESC
                LIMIT " . (!empty($_GET['limit']) ? (int)$_GET['limit'] : 5);
        } else {
            // เรียงตาม rating
            $sql = "
                SELECT s.stu_name AS name,
                       AVG(r.rating) AS avg_rating
                FROM student s
                LEFT JOIN review r ON s.student_id = r.student_id
            ";
            
            // เพิ่มเงื่อนไข WHERE เริ่มต้น
            $sql .= " WHERE 1=1"; // เริ่มต้นด้วย WHERE ที่เป็นจริงเสมอ
            
            // เพิ่มเงื่อนไข major
            if (!empty($_GET['major'])) {
                $major = (int)$_GET['major'];
                $sql .= " AND s.major_id = $major";
            }
            
            // เพิ่มเงื่อนไข year
            if (!empty($_GET['year'])) {
                $year = (int)$_GET['year'];
                $sql .= " AND s.year = $year";
            }
            
            // เพิ่มเงื่อนไข gender (เพิ่มใหม่)
            if (!empty($_GET['gender'])) {
                $gender = (int)$_GET['gender'];
                $sql .= " AND s.gender_id = $gender";
            }
            
            // เพิ่มเงื่อนไข skill (เพิ่มใหม่)
            if (!empty($_GET['skill'])) {
                $skill = (int)$_GET['skill'];
                $sql .= " AND EXISTS (SELECT 1 FROM student_skill ss WHERE ss.student_id = s.student_id AND ss.skill_id = $skill)";
            }
            
            // เพิ่มเงื่อนไข subskill (เพิ่มใหม่)
            if (!empty($_GET['subskill'])) {
                $subskill = (int)$_GET['subskill'];
                $sql .= " AND EXISTS (SELECT 1 FROM student_skill ss WHERE ss.student_id = s.student_id AND ss.subskill_id = $subskill)";
            }
            
            // เพิ่มเงื่อนไข hobby (เพิ่มใหม่)
            if (!empty($_GET['hobby'])) {
                $hobby = (int)$_GET['hobby'];
                $sql .= " AND EXISTS (SELECT 1 FROM student_hobby sh WHERE sh.student_id = s.student_id AND sh.hobby_id = $hobby)";
            }
            
            // เพิ่มเงื่อนไข subhobby (เพิ่มใหม่)
            if (!empty($_GET['subhobby'])) {
                $subhobby = (int)$_GET['subhobby'];
                $sql .= " AND EXISTS (SELECT 1 FROM student_hobby sh WHERE sh.student_id = s.student_id AND sh.subhobby_id = $subhobby)";
            }
            
            $sql .= "
                GROUP BY s.student_id, s.stu_name
                ORDER BY avg_rating DESC
                LIMIT " . (!empty($_GET['limit']) ? (int)$_GET['limit'] : 5);
        }
        break;

    case 'supply-demand-skills':
        error_log("Supply-demand-skills received skill parameter: " . ($_GET['skill'] ?? 'none'));
        // Base query ที่ซับซ้อนขึ้นเพื่อรองรับ major
        $sql = "
            SELECT 
                s.skill_id,
                s.skill_name,
                (
                    SELECT COUNT(*) 
                    FROM student st 
                    WHERE EXISTS (SELECT 1 FROM student_skill sts WHERE sts.student_id = st.student_id AND sts.skill_id = s.skill_id)
        ";
        
        // เพิ่มเงื่อนไข WHERE สำหรับ supply (นิสิต)
        $supplyWhereClause = [];
        
        if (!empty($_GET['major'])) {
            $major_id = (int)$_GET['major'];
            $supplyWhereClause[] = "st.major_id = $major_id";
        }
        
        if (!empty($_GET['year'])) {
            $year = (int)$_GET['year'];
            $supplyWhereClause[] = "st.year = $year";
        }
        
        if (!empty($_GET['gender'])) {
            $gender = (int)$_GET['gender'];
            $supplyWhereClause[] = "st.gender_id = $gender";
        }
        
        if (!empty($_GET['hobby'])) {
            $hobby = (int)$_GET['hobby'];
            $supplyWhereClause[] = "EXISTS (SELECT 1 FROM student_hobby sh WHERE sh.student_id = st.student_id AND sh.hobby_id = $hobby)";
        }
        
        if (!empty($_GET['subhobby'])) {
            $subhobby = (int)$_GET['subhobby'];
            $supplyWhereClause[] = "EXISTS (SELECT 1 FROM student_hobby sh WHERE sh.student_id = st.student_id AND sh.subhobby_id = $subhobby)";
        }
        
        if (!empty($supplyWhereClause)) {
            $sql .= " AND " . implode(" AND ", $supplyWhereClause);
        }
        
        $sql .= "
                ) AS supply,
                (
                    SELECT COUNT(*) 
                    FROM post_job pj 
                    WHERE EXISTS (SELECT 1 FROM post_job_skill pjs WHERE pjs.post_job_id = pj.post_job_id AND pjs.skill_id = s.skill_id)
        ";
        
        // สำหรับความสมบูรณ์ อาจเพิ่มเงื่อนไขสำหรับ demand (งาน) ด้วย
        $demandWhereClause = [];
        
        if (!empty($_GET['category'])) {
            $category_id = (int)$_GET['category'];
            $demandWhereClause[] = "pj.job_category_id = $category_id";
        }
        
        if (!empty($_GET['subcategory'])) {
            $subcategory_id = (int)$_GET['subcategory'];
            $demandWhereClause[] = "pj.job_subcategory_id = $subcategory_id";
        }
        
        if (!empty($_GET['status'])) {
            $status_id = (int)$_GET['status'];
            $demandWhereClause[] = "pj.job_status_id = $status_id";
        }
        
        if (!empty($_GET['start']) && !empty($_GET['end'])) {
            $start = $_GET['start'];
            $end = $_GET['end'];
            $demandWhereClause[] = "pj.created_at BETWEEN '$start' AND '$end'";
        } else {
            if (!empty($_GET['start'])) {
                $start = $_GET['start'];
                $demandWhereClause[] = "pj.created_at >= '$start'";
            }
            if (!empty($_GET['end'])) {
                $end = $_GET['end'];
                $demandWhereClause[] = "pj.created_at <= '$end'";
            }
        }
        
        if (!empty($demandWhereClause)) {
            $sql .= " AND " . implode(" AND ", $demandWhereClause);
        }
        
        $sql .= "
                ) AS demand
            FROM skill s
        ";
        
        // เพิ่มเงื่อนไขกรองทักษะเฉพาะ
        if (!empty($_GET['skill'])) {
            $skill_id = (int)$_GET['skill'];
            $sql .= " WHERE s.skill_id = $skill_id";
            error_log("Filtering supply-demand-skills by skill_id: $skill_id");
        }
        
        if (!empty($_GET['subskill'])) {
            $subskill_id = (int)$_GET['subskill'];
            $sql .= " WHERE EXISTS (SELECT 1 FROM subskill ss WHERE ss.skill_id = s.skill_id AND ss.subskill_id = $subskill_id)";
        }
        
        $sql .= " ORDER BY demand DESC";
        break;

        case 'job-duration-stats':
            // สมมติว่า job_status=2 คือปิดแล้ว
            // ใช้ TIMESTAMPDIFF(DAY, created_at, job_end) เป็น duration
            $sql = "
                SELECT 
                    post_job_id,
                    title,
                    TIMESTAMPDIFF(DAY, created_at, job_end) as duration_days
                FROM post_job
                WHERE job_status_id=2
            ";
            
            // Append additional filters if available
            if (!empty($whereClause)) {
                $sql .= preg_replace('/^WHERE/i', 'AND', $whereClause, 1);
            }
            
            $sql .= " ORDER BY duration_days DESC";
            
            // execute
            $result = $db->query($sql);
            $rows = [];
            while($r = $result->fetch_assoc()){
                $rows[] = $r;
            }
            echo json_encode($rows);
            exit;
            break;

    case 'top-teachers-by-success':
        /*
            ไอเดีย: success = #jobs accepted & completed / #jobs posted
            สมมติ close job=2 => success
        */
        $sql = "
            SELECT t.teach_name as name,
                    COUNT(pj.post_job_id) as total_posts,
                    SUM(CASE WHEN pj.job_status_id=2 THEN 1 ELSE 0 END) as success_count
            FROM teacher t
            LEFT JOIN post_job pj ON pj.teacher_id = t.teacher_id
        ";
        
        // Append the where clause
        if (!empty($whereClause)) {
            $sql .= preg_replace('/^WHERE pj/i', 'WHERE (pj', $whereClause, 1);
            $sql .= ")";
        }
        
        $sql .= "
            GROUP BY t.teacher_id, t.teach_name
            ORDER BY success_count DESC
            LIMIT 5
        ";
        break;
                     
    // Professor rating
    case 'professor-rating':
        $sql = "
            SELECT t.teach_name AS name,
            COUNT(cj.post_job_id) AS total_closings
        FROM teacher t
        LEFT JOIN post_job pj ON t.teacher_id = pj.teacher_id
        LEFT JOIN close_job cj ON pj.post_job_id = cj.post_job_id
        $whereClause
        GROUP BY t.teacher_id, t.teach_name
        ORDER BY total_closings DESC
        ";
        break;

    default:
        echo json_encode(["error" => "Invalid API endpoint"]);
        exit;
}

if (!$sql) {
    handleError("Invalid API endpoint or SQL query could not be constructed");
}

$result = $db->query($sql);
if (!$result) {
    handleError("Database query failed: " . $db->error, ["sql" => $sql]);
}

$data = [];
while ($row = $result->fetch_assoc()) {
    
    $data[] = $row;
}

echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
handleError("Exception: " . $e->getMessage(), [
    "file" => $e->getFile(),
    "line" => $e->getLine()
]);
}

$db->close();