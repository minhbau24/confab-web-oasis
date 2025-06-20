<?php
/**
 * Trang chi tiết hội nghị
 */
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'classes/Conference.php';

// Kiểm tra ID hội nghị
$conferenceId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($conferenceId <= 0) {
    // Chuyển hướng về trang danh sách nếu không có ID
    header('Location: conferences.php');
    exit;
}

// Khởi tạo đối tượng Conference
$conferenceObj = new Conference();

// Lấy thông tin chi tiết hội nghị
$conference = $conferenceObj->getConferenceById($conferenceId);

// Nếu không tìm thấy hội nghị, chuyển hướng về trang danh sách
if (!$conference) {
    header('Location: conferences.php?error=not_found');
    exit;
}

// Lấy thông tin người dùng đăng nhập
$isLoggedIn = isset($_SESSION['user_id']);
$userId = $isLoggedIn ? $_SESSION['user_id'] : 0;
$userName = $isLoggedIn ? $_SESSION['user_name'] : "Đăng nhập";
$userRole = $isLoggedIn ? $_SESSION['user_role'] : "guest";

// Kiểm tra xem người dùng đã đăng ký tham gia hội nghị này chưa
$isRegistered = false;
if ($isLoggedIn) {
    $isRegistered = $conferenceObj->isUserRegistered($userId, $conferenceId);
}

// Xử lý đăng ký tham gia hội nghị
if (isset($_POST['register']) && $isLoggedIn) {
    $result = $conferenceObj->registerConference($userId, $conferenceId);
    if ($result['success']) {
        $isRegistered = true;
        $successMessage = $result['message'];
    } else {
        $errorMessage = $result['message'];
    }
}

// Xử lý hủy đăng ký tham gia
if (isset($_POST['unregister']) && $isLoggedIn) {
    $result = $conferenceObj->unregisterConference($userId, $conferenceId);
    if ($result['success']) {
        $isRegistered = false;
        $successMessage = $result['message'];
    } else {
        $errorMessage = $result['message'];
    }
}

// Lấy thông tin diễn giả
$speakers = $conferenceObj->getConferenceSpeakers($conferenceId);

// Lấy lịch trình hội nghị
$schedule = $conferenceObj->getConferenceSchedule($conferenceId);

// Lấy mục tiêu hội nghị
$objectives = $conferenceObj->getConferenceObjectives($conferenceId);

// Lấy đối tượng tham dự
$audience = $conferenceObj->getConferenceAudience($conferenceId);

// Lấy FAQ
$faqs = $conferenceObj->getConferenceFaq($conferenceId);

// Lấy hội nghị liên quan (chung category)
$relatedConferences = $conferenceObj->getRelatedConferences($conferenceId, $conference['category'], 3);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $conference['title']; ?> - Trung tâm Hội nghị</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 70px;
            /* Đảm bảo nội dung không bị che khuất bởi thanh navbar fixed */
        }

        .hero-banner {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), 
                url('<?php echo !empty($conference['image']) ? $conference['image'] : 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1200&h=600&fit=crop'; ?>');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 120px 0 80px;
            margin-top: -70px;
            /* Bù trừ padding-top của body để hero section tiếp nối với navbar */
        }

        .conference-card {
            transition: transform 0.3s ease;
        }

        .conference-card:hover {
            transform: translateY(-5px);
        }

        .speaker-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .speaker-card:hover {
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
            transform: translateY(-3px);
        }

        .timeline-item {
            border-left: 3px solid #007bff;
            padding-left: 20px;
            margin-bottom: 30px;
            position: relative;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -9px;
            top: 0;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: #007bff;
        }

        .timeline-time {
            font-weight: bold;
            color: #007bff;
        }

        .list-icon {
            width: 30px;
            height: 30px;
            background: #007bff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .registration-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Hero Banner -->
    <section class="hero-banner">
        <div class="container text-center">
            <h1 class="display-4"><?php echo $conference['title']; ?></h1>
            <p class="lead"><?php echo htmlspecialchars(substr($conference['description'], 0, 150)) . (strlen($conference['description']) > 150 ? '...' : ''); ?></p>
            <div class="d-flex justify-content-center mt-4">
                <div class="me-4">
                    <i class="fas fa-calendar me-2"></i>
                    <?php 
                        $startDate = new DateTime($conference['date']);
                        $endDate = !empty($conference['endDate']) ? new DateTime($conference['endDate']) : null;
                        
                        echo $startDate->format('d/m/Y');
                        if ($endDate && $startDate->format('Y-m-d') !== $endDate->format('Y-m-d')) {
                            echo ' - ' . $endDate->format('d/m/Y');
                        }
                    ?>
                </div>
                <div class="me-4">
                    <i class="fas fa-map-marker-alt me-2"></i><?php echo $conference['location']; ?>
                </div>
                <div>
                    <i class="fas fa-users me-2"></i><?php echo $conference['attendees']; ?>/<?php echo $conference['capacity']; ?> người tham dự
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row">
            <!-- Left Column - Conference Details -->
            <div class="col-lg-8">
                <!-- Conference Description -->
                <section class="mb-5">
                    <h2 class="mb-4">Giới thiệu</h2>
                    <div class="card">
                        <div class="card-body">
                            <p><?php echo nl2br(htmlspecialchars($conference['description'])); ?></p>
                            
                            <?php if (!empty($objectives)): ?>
                            <h4 class="mt-4 mb-3">Mục tiêu Hội nghị</h4>
                            <ul class="list-unstyled">
                                <?php foreach ($objectives as $objective): ?>
                                <li class="d-flex align-items-start mb-3">
                                    <div class="list-icon">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div><?php echo htmlspecialchars($objective['description']); ?></div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                            
                            <?php if (!empty($audience)): ?>
                            <h4 class="mt-4 mb-3">Đối tượng tham dự</h4>
                            <ul class="list-unstyled">
                                <?php foreach ($audience as $audienceItem): ?>
                                <li class="d-flex align-items-start mb-3">
                                    <div class="list-icon">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div><?php echo htmlspecialchars($audienceItem['description']); ?></div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

                <!-- Speakers Section -->
                <?php if (!empty($speakers)): ?>
                <section class="mb-5">
                    <h2 class="mb-4">Diễn giả</h2>
                    <div class="row">
                        <?php foreach ($speakers as $speaker): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card speaker-card h-100">
                                <div class="row g-0">
                                    <div class="col-4">
                                        <img src="<?php echo !empty($speaker['image']) ? $speaker['image'] : 'https://via.placeholder.com/150?text=Speaker'; ?>" 
                                             class="img-fluid rounded-start h-100" style="object-fit: cover;" 
                                             alt="<?php echo $speaker['name']; ?>">
                                    </div>
                                    <div class="col-8">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo $speaker['name']; ?></h5>
                                            <p class="card-subtitle text-muted mb-2"><?php echo $speaker['title']; ?></p>
                                            <p class="card-text small"><?php echo htmlspecialchars($speaker['bio']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>

                <!-- Schedule Section -->
                <?php if (!empty($schedule)): ?>
                <section class="mb-5">
                    <h2 class="mb-4">Lịch trình</h2>
                    <div class="card">
                        <div class="card-body">
                            <?php
                            $currentDate = '';
                            foreach ($schedule as $item):
                                $eventDate = new DateTime($item['eventDate']);
                                $formattedDate = $eventDate->format('d/m/Y');
                                
                                // Hiển thị ngày nếu khác với ngày trước đó
                                if ($formattedDate !== $currentDate):
                                    $currentDate = $formattedDate;
                            ?>
                                <h4 class="mt-4 mb-3"><?php echo $formattedDate; ?></h4>
                            <?php endif; ?>
                                
                                <div class="timeline-item">
                                    <div class="timeline-time"><?php echo $item['startTime'] . ' - ' . $item['endTime']; ?></div>
                                    <h5 class="mt-2"><?php echo $item['title']; ?></h5>
                                    <?php if (!empty($item['speaker'])): ?>
                                    <div class="text-muted">Diễn giả: <?php echo $item['speaker']; ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($item['description'])): ?>
                                    <p class="mt-2"><?php echo htmlspecialchars($item['description']); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
                <?php endif; ?>

                <!-- FAQs Section -->
                <?php if (!empty($faqs)): ?>
                <section class="mb-5">
                    <h2 class="mb-4">Câu hỏi thường gặp</h2>
                    <div class="accordion" id="faqAccordion">
                        <?php foreach ($faqs as $index => $faq): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="false"
                                    aria-controls="collapse<?php echo $index; ?>">
                                    <?php echo htmlspecialchars($faq['question']); ?>
                                </button>
                            </h2>
                            <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse" 
                                 aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>
                
                <!-- Organizer Info -->
                <section class="mb-5">
                    <h2 class="mb-4">Thông tin Ban Tổ chức</h2>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $conference['organizer_name'] ?? 'Không có thông tin'; ?></h5>
                            <?php if (!empty($conference['organizer_email']) || !empty($conference['organizer_phone'])): ?>
                            <p class="card-text">
                                <?php if (!empty($conference['organizer_email'])): ?>
                                <i class="fas fa-envelope me-2"></i>Email: <?php echo $conference['organizer_email']; ?><br>
                                <?php endif; ?>
                                
                                <?php if (!empty($conference['organizer_phone'])): ?>
                                <i class="fas fa-phone me-2"></i>Điện thoại: <?php echo $conference['organizer_phone']; ?>
                                <?php endif; ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Right Column - Registration & Related Info -->
            <div class="col-lg-4">
                <!-- Registration Box -->
                <div class="registration-box mb-4">
                    <?php if (isset($successMessage)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $successMessage; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($errorMessage)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $errorMessage; ?>
                    </div>
                    <?php endif; ?>
                    
                    <h3 class="mb-3">Thông tin đăng ký</h3>
                    <div class="mb-3 d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-money-bill fa-2x text-success"></i>
                        </div>
                        <div>
                            <div class="text-muted">Giá vé:</div>
                            <div class="fs-5">
                                <?php 
                                    if ($conference['price'] > 0) {
                                        echo number_format($conference['price'], 0, ',', '.') . ' VNĐ';
                                    } else {
                                        echo 'Miễn phí';
                                    }
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3 d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                        <div>
                            <div class="text-muted">Số lượng:</div>
                            <div class="fs-5"><?php echo $conference['attendees']; ?>/<?php echo $conference['capacity']; ?> người tham dự</div>
                            <?php if ($conference['attendees'] >= $conference['capacity']): ?>
                                <div class="text-danger">Đã hết chỗ</div>
                            <?php else: ?>
                                <div class="text-success">Còn <?php echo $conference['capacity'] - $conference['attendees']; ?> chỗ</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mb-4 d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-calendar-alt fa-2x text-warning"></i>
                        </div>
                        <div>
                            <div class="text-muted">Thời gian:</div>
                            <div class="fs-5">
                                <?php 
                                    echo $startDate->format('d/m/Y');
                                    if ($endDate && $startDate->format('Y-m-d') !== $endDate->format('Y-m-d')) {
                                        echo ' - ' . $endDate->format('d/m/Y');
                                    }
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($isLoggedIn): ?>
                        <?php if ($isRegistered): ?>
                            <form method="post" action="conference-detail.php?id=<?php echo $conferenceId; ?>">
                                <button type="submit" name="unregister" class="btn btn-outline-danger w-100">
                                    <i class="fas fa-times-circle me-2"></i>Hủy đăng ký
                                </button>
                            </form>
                        <?php else: ?>
                            <?php if ($conference['status'] == 'active' && $conference['attendees'] < $conference['capacity']): ?>
                                <form method="post" action="conference-detail.php?id=<?php echo $conferenceId; ?>">
                                    <button type="submit" name="register" class="btn btn-primary w-100">
                                        <i class="fas fa-check-circle me-2"></i>Đăng ký tham dự
                                    </button>
                                </form>
                            <?php elseif ($conference['status'] == 'completed'): ?>
                                <button class="btn btn-secondary w-100" disabled>
                                    <i class="fas fa-calendar-check me-2"></i>Hội nghị đã kết thúc
                                </button>
                            <?php elseif ($conference['status'] == 'cancelled'): ?>
                                <button class="btn btn-secondary w-100" disabled>
                                    <i class="fas fa-ban me-2"></i>Hội nghị đã hủy
                                </button>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100" disabled>
                                    <i class="fas fa-users-slash me-2"></i>Hết chỗ
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="login.php?redirect=conference-detail.php?id=<?php echo $conferenceId; ?>" class="btn btn-primary w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập để đăng ký
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Share & Save -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Chia sẻ hội nghị</h5>
                        <div class="d-flex mt-2">
                            <a href="#" class="btn btn-outline-primary me-2"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="btn btn-outline-info me-2"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="btn btn-outline-danger me-2"><i class="fab fa-google"></i></a>
                            <a href="#" class="btn btn-outline-success me-2"><i class="fab fa-whatsapp"></i></a>
                            <a href="#" class="btn btn-outline-secondary"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Related Conferences -->
                <?php if (!empty($relatedConferences)): ?>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Hội nghị liên quan</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($relatedConferences as $related): ?>
                            <li class="list-group-item">
                                <a href="conference-detail.php?id=<?php echo $related['id']; ?>" class="text-decoration-none">
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo !empty($related['image']) ? $related['image'] : 'https://via.placeholder.com/50?text=Conference'; ?>" 
                                             class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;" 
                                             alt="<?php echo $related['title']; ?>">
                                        <div>
                                            <h6 class="mb-0"><?php echo $related['title']; ?></h6>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo (new DateTime($related['date']))->format('d/m/Y'); ?>
                                            </small>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
