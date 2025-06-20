-- --------------------------------------------------------
-- Dữ liệu mẫu cho dự án Confab Web - Hệ thống quản lý Hội nghị
-- Tác giả: Hệ thống Quản lý Hội nghị
-- Ngày tạo: 20/06/2025
-- --------------------------------------------------------

USE `confab_db`;

-- --------------------------------------------------------
-- Dữ liệu mẫu cho bảng `users` - Người dùng
-- --------------------------------------------------------
INSERT INTO `users` (`firstName`, `lastName`, `email`, `password`, `role`, `phone`, `avatar`, `bio`) VALUES
-- Admin user (password: admin123)
('Admin', 'System', 'admin@example.com', '$2y$10$kspyQnTdJp.ewiNGMCWvYu.YOjK0s1yMj.jldzb15U4hKLvQg/qPC', 'admin', '0123456789', NULL, 'Quản trị viên hệ thống'),

-- Organizer users (password: password123)
('Nguyễn', 'Văn Tổ Chức', 'organizer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'organizer', '0987654321', NULL, 'Tổ chức sự kiện chuyên nghiệp với nhiều năm kinh nghiệm'),
('Trần', 'Minh Tuấn', 'tuan@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'organizer', '0909123456', NULL, 'Giám đốc Công ty tổ chức sự kiện ABC'),
('Lê', 'Thị Hương', 'huong@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'organizer', '0912345678', NULL, 'Chuyên gia tổ chức hội nghị quốc tế'),

-- Regular users (password: password123)
('Phạm', 'Văn Nam', 'nam@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '0978123456', NULL, 'Kỹ sư phần mềm tại Tech Company'),
('Vũ', 'Thị Lan', 'lan@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '0918765432', NULL, 'Giáo viên trường THPT Chu Văn An'),
('Đỗ', 'Hoàng Minh', 'minh@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '0967891234', NULL, 'Sinh viên ngành Công nghệ thông tin'),
('Hoàng', 'Thị Mai', 'mai@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '0956781234', NULL, 'Bác sĩ tại Bệnh viện Bạch Mai'),
('Trịnh', 'Văn Hải', 'hai@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '0945678912', NULL, 'Nhà thiết kế UX/UI');

-- --------------------------------------------------------
-- Dữ liệu mẫu cho bảng `speakers` - Diễn giả
-- --------------------------------------------------------
INSERT INTO `speakers` (`name`, `title`, `bio`, `image`) VALUES
('Nguyễn Thị Minh', 'CEO, InnovateTech Vietnam', 'Chuyên gia hàng đầu về AI và học máy với hơn 15 năm kinh nghiệm. Bà Minh đã tham gia nhiều dự án công nghệ lớn tại Việt Nam và quốc tế.', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400&h=400&fit=crop'),
('Trần Đức Khải', 'CTO, VietStartup', 'Tiên phong trong công nghệ blockchain và là nhà đồng sáng lập của nhiều startup thành công trong lĩnh vực fintech.', 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=400&h=400&fit=crop'),
('Phạm Thị Hương', 'Founder, GreenTech Solutions', 'Chuyên gia về phát triển bền vững và năng lượng sạch. Người sáng lập GreenTech Solutions - công ty tiên phong trong các giải pháp năng lượng tái tạo tại Việt Nam.', 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=400&h=400&fit=crop'),
('Lê Văn Bách', 'AI Research Director, FPT Software', 'Tiến sĩ AI với hơn 15 năm kinh nghiệm nghiên cứu và ứng dụng thực tế. Ông Bách là tác giả của nhiều công trình nghiên cứu được công bố quốc tế.', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&h=400&fit=crop'),
('Đặng Thị Thu Thảo', 'Marketing Director, Sendo', 'Chuyên gia marketing với hơn 10 năm kinh nghiệm trong lĩnh vực thương mại điện tử. Bà Thảo đã góp phần đưa Sendo trở thành một trong những sàn TMĐT hàng đầu Việt Nam.', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400&h=400&fit=crop'),
('Ngô Quang Vinh', 'Professor, Vietnam National University', 'Giáo sư ngành Giáo dục học với nhiều đóng góp quan trọng trong việc cải cách giáo dục đại học tại Việt Nam.', 'https://images.unsplash.com/photo-1566492031773-4f4e44671857?w=400&h=400&fit=crop'),
('Trần Minh Phương', 'Healthcare Specialist, WHO Vietnam', 'Chuyên gia y tế công cộng với kinh nghiệm làm việc tại WHO và các tổ chức y tế lớn. Bà Phương đã đóng góp vào nhiều chương trình cải thiện y tế cộng đồng.', 'https://images.unsplash.com/photo-1567532939604-b6b5b0db2604?w=400&h=400&fit=crop'),
('Vũ Quang Trí', 'Business Consultant', 'Cố vấn kinh doanh với hơn 20 năm kinh nghiệm tư vấn cho các doanh nghiệp vừa và nhỏ tại Việt Nam. Ông Trí đã giúp nhiều startup gọi vốn thành công.', 'https://images.unsplash.com/photo-1563237023-b1e970526dcb?w=400&h=400&fit=crop');

-- --------------------------------------------------------
-- Dữ liệu mẫu cho bảng `conferences` - Hội nghị
-- --------------------------------------------------------
INSERT INTO `conferences` (`title`, `description`, `date`, `endDate`, `location`, `category`, `price`, `capacity`, `attendees`, `status`, `image`, `organizer_name`, `organizer_email`, `organizer_phone`, `created_by`) VALUES
-- Hội nghị 1
('Vietnam Tech Summit 2025', 'Sự kiện công nghệ hàng đầu Việt Nam quy tụ các công ty khởi nghiệp tiên phong, ra mắt công nghệ đột phá, và kết nối các chuyên gia trong ngành. Hội nghị sẽ tập trung vào các xu hướng AI, Machine Learning, Blockchain và chuyển đổi số.', '2025-09-15', '2025-09-17', 'TP. Hồ Chí Minh', 'Công nghệ', 1999000, 3000, 2600, 'active', 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=400&fit=crop', 'VnTech Media', 'events@vntech.com.vn', '+84 28 1234 5678', 2),

-- Hội nghị 2
('Hội nghị Y tế Quốc tế 2025', 'Hội nghị y tế quốc tế mang đến những chia sẻ và cập nhật mới nhất trong nghiên cứu, điều trị và công nghệ y tế toàn cầu. Sự kiện sẽ có sự tham gia của các chuyên gia y tế hàng đầu từ nhiều quốc gia và tổ chức y tế uy tín.', '2025-06-20', '2025-06-22', 'Hà Nội', 'Y tế', 2500000, 1500, 1200, 'active', 'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?w=800&h=400&fit=crop', 'Hiệp hội Y khoa Việt Nam', 'contact@vmassoc.org.vn', '+84 24 3762 5555', 3),

-- Hội nghị 3
('Hội thảo Marketing Số 2025', 'Khám phá các xu hướng, chiến lược và công cụ marketing số mới nhất để thúc đẩy doanh nghiệp của bạn trong kỷ nguyên số. Hội thảo sẽ bao gồm các buổi thảo luận về SEO, Content Marketing, Social Media và các chiến lược Growth Hacking.', '2025-07-10', '2025-07-11', 'Đà Nẵng', 'Marketing', 1500000, 800, 650, 'active', 'https://images.unsplash.com/photo-1557804506-669a67965ba0?w=800&h=400&fit=crop', 'Digital Marketing Association', 'info@dma.vn', '+84 28 3915 3782', 2),

-- Hội nghị 4
('Diễn đàn Giáo dục Việt Nam 2025', 'Diễn đàn thường niên tập trung về đổi mới giáo dục, phương pháp giảng dạy hiện đại và ứng dụng công nghệ trong giáo dục tại Việt Nam. Sự kiện mang đến cơ hội chia sẻ kinh nghiệm và kết nối giữa các nhà giáo dục.', '2025-08-25', '2025-08-27', 'Hà Nội', 'Giáo dục', 1200000, 1000, 850, 'active', 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=800&h=400&fit=crop', 'Bộ Giáo dục và Đào tạo', 'forum@moet.gov.vn', '+84 24 3869 4585', 3),

-- Hội nghị 5
('Hội thảo Phát triển Bền vững 2025', 'Hội thảo về các giải pháp phát triển bền vững và bảo vệ môi trường, tập trung vào các mô hình kinh tế tuần hoàn và giảm thiểu tác động môi trường. Sự kiện quy tụ các chuyên gia môi trường, doanh nghiệp xanh và các tổ chức phi chính phủ.', '2025-10-05', '2025-10-07', 'Cần Thơ', 'Môi trường', 1000000, 600, 450, 'active', 'https://images.unsplash.com/photo-1569098644584-210bcd375b59?w=800&h=400&fit=crop', 'Vietnam Sustainability Forum', 'contact@vsf.org.vn', '+84 28 3917 4565', 4),

-- Hội nghị 6
('FinTech Connect Vietnam 2025', 'Sự kiện kết nối các startup, ngân hàng, tổ chức tài chính và nhà đầu tư trong lĩnh vực công nghệ tài chính. Hội nghị sẽ giới thiệu các giải pháp thanh toán, blockchain, ngân hàng số và các xu hướng fintech mới nhất.', '2025-11-12', '2025-11-13', 'TP. Hồ Chí Minh', 'Công nghệ', 2200000, 1200, 980, 'active', 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=800&h=400&fit=crop', 'Vietnam FinTech Association', 'info@vnfintech.org.vn', '+84 28 3825 7643', 2),

-- Hội nghị 7
('Hội nghị Kiến trúc & Thiết kế 2025', 'Diễn đàn chia sẻ kiến thức và cảm hứng về kiến trúc, thiết kế nội thất và quy hoạch đô thị hiện đại tại Việt Nam. Sự kiện sẽ trưng bày các dự án tiêu biểu và thảo luận về xu hướng thiết kế bền vững.', '2025-09-28', '2025-09-30', 'Hà Nội', 'Thiết kế', 1800000, 900, 780, 'active', 'https://images.unsplash.com/photo-1432888622747-4eb9a8efeb07?w=800&h=400&fit=crop', 'Hội Kiến trúc sư Việt Nam', 'contact@vaa.org.vn', '+84 24 3936 4913', 4),

-- Hội nghị 8
('Hội nghị Quốc tế về Trí tuệ Nhân tạo 2025', 'Hội nghị quốc tế về các xu hướng và ứng dụng mới nhất trong lĩnh vực trí tuệ nhân tạo. Sự kiện sẽ có sự tham gia của các nhà nghiên cứu và chuyên gia AI từ các tập đoàn công nghệ hàng đầu thế giới.', '2025-12-08', '2025-12-10', 'TP. Hồ Chí Minh', 'Công nghệ', 2500000, 2000, 1750, 'active', 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=800&h=400&fit=crop', 'Vietnam AI Association', 'info@vietnamai.org', '+84 28 3824 1975', 3),

-- Hội nghị 9 (đã kết thúc)
('Hội thảo Digital Transformation 2025', 'Hội thảo về quá trình chuyển đổi số trong doanh nghiệp, chia sẻ các chiến lược và công cụ để doanh nghiệp thích nghi với kỷ nguyên số. Các chuyên đề bao gồm: tự động hóa quy trình, chuyển đổi văn hóa doanh nghiệp và bảo mật dữ liệu.', '2025-05-20', '2025-05-21', 'Hà Nội', 'Công nghệ', 1700000, 1000, 950, 'completed', 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=800&h=400&fit=crop', 'Vietnam Digital Transformation Alliance', 'contact@vdta.vn', '+84 24 3559 2846', 2),

-- Hội nghị 10 (sắp diễn ra)
('Startup Connect 2025', 'Sự kiện kết nối các startup với nhà đầu tư, mentor và các đối tác tiềm năng. Cơ hội để các doanh nghiệp khởi nghiệp giới thiệu ý tưởng, gọi vốn và mở rộng mạng lưới kinh doanh.', '2025-08-15', '2025-08-16', 'TP. Hồ Chí Minh', 'Kinh doanh', 1200000, 1500, 0, 'draft', 'https://images.unsplash.com/photo-1559223607-a43c990c692c?w=800&h=400&fit=crop', 'Vietnam Startup Network', 'hello@vnstartup.org', '+84 28 3824 6713', 4);

-- --------------------------------------------------------
-- Liên kết hội nghị với diễn giả
-- --------------------------------------------------------
INSERT INTO `conference_speakers` (`conference_id`, `speaker_id`) VALUES
(1, 1), (1, 2), (1, 4), -- Vietnam Tech Summit
(2, 7), (2, 3), (2, 6), -- Hội nghị Y tế
(3, 5), (3, 8), -- Marketing Số
(4, 6), (4, 3), (4, 7), -- Diễn đàn Giáo dục
(5, 3), (5, 8), -- Phát triển Bền vững
(6, 2), (6, 8), (6, 1), -- FinTech Connect
(7, 3), (7, 5), -- Kiến trúc & Thiết kế
(8, 1), (8, 4), (8, 2), -- Trí tuệ Nhân tạo
(9, 1), (9, 5), (9, 8), -- Digital Transformation
(10, 8), (10, 2), (10, 5); -- Startup Connect

-- --------------------------------------------------------
-- Dữ liệu mẫu cho lịch trình hội nghị (cho 3 hội nghị đầu tiên)
-- --------------------------------------------------------
-- Vietnam Tech Summit
INSERT INTO `conference_schedule` (`conference_id`, `eventDate`, `startTime`, `endTime`, `title`, `speaker`, `description`) VALUES
(1, '2025-09-15', '08:00:00', '09:00:00', 'Đăng ký & Chào mừng', NULL, 'Đăng ký tham dự và nhận tài liệu hội nghị'),
(1, '2025-09-15', '09:00:00', '10:30:00', 'Bài phát biểu khai mạc: Tương lai của AI tại Việt Nam', 'Nguyễn Thị Minh', 'Phân tích xu hướng và cơ hội phát triển AI tại Việt Nam trong 5 năm tới'),
(1, '2025-09-15', '10:45:00', '12:00:00', 'Ứng dụng Blockchain trong chuyển đổi số', 'Trần Đức Khải', 'Các case study thực tế về ứng dụng blockchain trong doanh nghiệp Việt Nam'),
(1, '2025-09-15', '13:30:00', '15:00:00', 'Machine Learning: Từ lý thuyết đến thực hành', 'Lê Văn Bách', 'Workshop thực hành về xây dựng và triển khai mô hình ML'),
(1, '2025-09-15', '15:15:00', '16:30:00', 'Panel: Khởi nghiệp công nghệ trong thời đại AI', 'Nhiều diễn giả', 'Thảo luận về cơ hội và thách thức cho startup công nghệ'),
(1, '2025-09-16', '09:00:00', '10:30:00', 'Bảo mật thông tin trong kỷ nguyên số', 'Chuyên gia bảo mật', 'Các giải pháp bảo mật và phòng chống tấn công mạng'),
(1, '2025-09-16', '10:45:00', '12:00:00', 'Cloud Computing và ứng dụng', 'Chuyên gia AWS', 'Giải pháp điện toán đám mây cho doanh nghiệp vừa và nhỏ'),
(1, '2025-09-16', '13:30:00', '15:00:00', 'Hackathon: Giải pháp AI cho bền vững', NULL, 'Cuộc thi lập trình tìm giải pháp AI cho các vấn đề môi trường'),
(1, '2025-09-17', '09:00:00', '10:30:00', 'DevOps: Tối ưu quy trình phát triển', 'Chuyên gia Google', 'Các phương pháp và công cụ DevOps hiện đại'),
(1, '2025-09-17', '10:45:00', '12:00:00', 'Tổng kết và trao giải Hackathon', NULL, 'Tổng kết hội nghị và trao giải cho đội thắng cuộc'),

-- Hội nghị Y tế
(2, '2025-06-20', '08:30:00', '09:30:00', 'Đăng ký & Chào mừng', NULL, 'Đón tiếp đại biểu và phát tài liệu'),
(2, '2025-06-20', '09:30:00', '11:00:00', 'Ứng dụng AI trong chẩn đoán hình ảnh y tế', 'TS. Trần Minh Phương', 'Các tiến bộ trong việc ứng dụng trí tuệ nhân tạo để phân tích và chẩn đoán hình ảnh y tế'),
(2, '2025-06-20', '11:15:00', '12:30:00', 'Đổi mới trong điều trị ung thư', 'GS. Nguyễn Chấn Hùng', 'Phương pháp điều trị mới và kết quả nghiên cứu gần đây'),
(2, '2025-06-20', '14:00:00', '15:30:00', 'Y tế công cộng sau đại dịch', 'Chuyên gia WHO', 'Bài học và chiến lược chuẩn bị cho các dịch bệnh trong tương lai'),
(2, '2025-06-21', '09:00:00', '10:30:00', 'Những tiến bộ trong y học tái tạo', 'GS. Phạm Văn An', 'Nghiên cứu mới về tế bào gốc và ứng dụng trong điều trị'),
(2, '2025-06-21', '10:45:00', '12:15:00', 'Hệ thống y tế số: Cơ hội và thách thức', 'Trần Minh Phương', 'Chuyển đổi số trong quản lý y tế và chăm sóc bệnh nhân'),
(2, '2025-06-22', '09:00:00', '10:30:00', 'Tọa đàm: Tương lai của ngành y tế Việt Nam', 'Nhiều chuyên gia', 'Thảo luận về định hướng phát triển ngành y tế trong thập kỷ tới'),

-- Hội thảo Marketing Số
(3, '2025-07-10', '08:30:00', '09:00:00', 'Check-in & Networking', NULL, 'Đăng ký tham dự và kết nối'),
(3, '2025-07-10', '09:00:00', '10:15:00', 'Content Marketing trong kỷ nguyên AI', 'Đặng Thị Thu Thảo', 'Chiến lược tạo nội dung hiệu quả với sự hỗ trợ của AI'),
(3, '2025-07-10', '10:30:00', '11:45:00', 'SEO 2025: Xu hướng mới nhất', 'Chuyên gia Cốc Cốc', 'Các thuật toán tìm kiếm mới và cách tối ưu'),
(3, '2025-07-10', '13:00:00', '14:15:00', 'Social Media Marketing: Từ chiến lược đến thực thi', 'Chuyên gia Meta', 'Cách xây dựng và thực hiện chiến dịch social media hiệu quả'),
(3, '2025-07-10', '14:30:00', '15:45:00', 'Phân tích dữ liệu Marketing', 'Vũ Quang Trí', 'Sử dụng dữ liệu lớn để tối ưu chiến dịch marketing'),
(3, '2025-07-11', '09:00:00', '10:15:00', 'Email Marketing: Cá nhân hóa và tự động hóa', 'Chuyên gia Marketing', 'Chiến lược email marketing hiệu quả trong kỷ nguyên số'),
(3, '2025-07-11', '10:30:00', '11:45:00', 'Panel: MarTech Stack cho SMEs', 'Nhiều diễn giả', 'Thảo luận về các công cụ marketing phù hợp cho doanh nghiệp vừa và nhỏ');

-- --------------------------------------------------------
-- Dữ liệu mẫu cho bảng conference_objectives - mục tiêu hội nghị
-- --------------------------------------------------------
-- Vietnam Tech Summit
INSERT INTO `conference_objectives` (`conference_id`, `description`, `order_num`) VALUES
(1, 'Cập nhật về các xu hướng công nghệ mới nhất trong AI, Blockchain và Machine Learning', 1),
(1, 'Kết nối các chuyên gia công nghệ, startup và nhà đầu tư trong hệ sinh thái công nghệ Việt Nam', 2),
(1, 'Chia sẻ kinh nghiệm triển khai các dự án chuyển đổi số thành công', 3),
(1, 'Giới thiệu các case study thực tế về ứng dụng công nghệ trong doanh nghiệp', 4),

-- Hội nghị Y tế
(2, 'Chia sẻ các tiến bộ y học mới nhất trong chẩn đoán và điều trị', 1),
(2, 'Thảo luận về các chiến lược nâng cao chất lượng chăm sóc sức khỏe tại Việt Nam', 2),
(2, 'Kết nối các chuyên gia y tế trong nước và quốc tế', 3),
(2, 'Giới thiệu các công nghệ mới trong lĩnh vực y tế và chăm sóc sức khỏe', 4),

-- Hội thảo Marketing Số
(3, 'Cập nhật các xu hướng marketing số mới nhất năm 2025', 1),
(3, 'Cung cấp các công cụ và chiến lược marketing hiệu quả cho doanh nghiệp', 2),
(3, 'Chia sẻ case study từ các chiến dịch marketing thành công', 3),
(3, 'Xây dựng mạng lưới kết nối giữa các chuyên gia marketing', 4);

-- --------------------------------------------------------
-- Dữ liệu mẫu cho bảng conference_audience - đối tượng tham dự
-- --------------------------------------------------------
-- Vietnam Tech Summit
INSERT INTO `conference_audience` (`conference_id`, `description`, `order_num`) VALUES
(1, 'Chuyên gia công nghệ thông tin và phát triển phần mềm', 1),
(1, 'Nhà quản lý IT và CTO tại các doanh nghiệp', 2),
(1, 'Startup công nghệ và các nhà đầu tư', 3),
(1, 'Sinh viên và giảng viên ngành CNTT', 4),

-- Hội nghị Y tế
(2, 'Bác sĩ và chuyên gia y tế từ các bệnh viện và phòng khám', 1),
(2, 'Nhà nghiên cứu trong lĩnh vực y học và khoa học sức khỏe', 2),
(2, 'Quản lý các cơ sở y tế và bệnh viện', 3),
(2, 'Đại diện các công ty dược phẩm và thiết bị y tế', 4),

-- Hội thảo Marketing Số
(3, 'Giám đốc tiếp thị và các chuyên gia marketing', 1),
(3, 'Chủ doanh nghiệp vừa và nhỏ', 2),
(3, 'Freelancer và agency marketing', 3),
(3, 'Nhà phân tích dữ liệu marketing', 4);

-- --------------------------------------------------------
-- Dữ liệu mẫu cho bảng conference_faq - câu hỏi thường gặp
-- --------------------------------------------------------
-- Vietnam Tech Summit
INSERT INTO `conference_faq` (`conference_id`, `question`, `answer`, `order_num`) VALUES
(1, 'Hội nghị có hỗ trợ phiên dịch tiếng Anh không?', 'Có, tất cả các phiên thảo luận chính đều có phiên dịch song song tiếng Anh - tiếng Việt.', 1),
(1, 'Tôi có thể tham gia hackathon nếu không có kinh nghiệm lập trình AI không?', 'Được! Hackathon có nhiều vai trò khác nhau, bao gồm thiết kế, phân tích kinh doanh và thuyết trình. Bạn có thể tham gia và học hỏi từ các thành viên khác.', 2),
(1, 'Nội dung của hội nghị có được cung cấp sau khi kết thúc không?', 'Tất cả các bài thuyết trình và tài liệu sẽ được gửi cho người tham dự qua email sau khi hội nghị kết thúc.', 3),
(1, 'Có giảm giá cho sinh viên không?', 'Có, sinh viên được giảm 50% giá vé khi đăng ký với email trường học và thẻ sinh viên hợp lệ.', 4),

-- Hội nghị Y tế
(2, 'Hội nghị có tính điểm CME không?', 'Có, tham dự đầy đủ hội nghị sẽ được cấp 15 điểm CME được công nhận bởi Bộ Y tế.', 1),
(2, 'Có thể đăng ký tham dự theo từng ngày không?', 'Có thể, nhưng chúng tôi khuyến khích tham gia trọn gói để có trải nghiệm tốt nhất và không bỏ lỡ các phiên quan trọng.', 2),
(2, 'Các bài nghiên cứu có thể được trình bày tại hội nghị không?', 'Chúng tôi có phiên poster và trình bày nghiên cứu ngắn. Vui lòng gửi abstract trước ngày 20/5/2025.', 3),
(2, 'Sẽ có triển lãm kèm theo hội nghị không?', 'Có, triển lãm các thiết bị y tế và dược phẩm mới sẽ diễn ra song song với hội nghị.', 4),

-- Hội thảo Marketing Số
(3, 'Workshop có giới hạn số người tham dự không?', 'Có, mỗi workshop sẽ giới hạn 30 người để đảm bảo chất lượng. Vui lòng đăng ký trước để đảm bảo chỗ.', 1),
(3, 'Tôi có thể mang theo laptop để thực hành không?', 'Đúng vậy, chúng tôi khuyến khích mang theo laptop để có thể thực hành ngay trong các buổi workshop.', 2),
(3, 'Có cơ hội việc làm hoặc kết nối với nhà tuyển dụng không?', 'Có, chúng tôi có phiên Networking đặc biệt vào cuối ngày đầu tiên, với sự tham gia của các nhà tuyển dụng trong ngành.', 3),
(3, 'Hội thảo có phù hợp với người mới bắt đầu không?', 'Hội thảo có nội dung cho mọi cấp độ. Chúng tôi có track dành riêng cho người mới bắt đầu với các khái niệm cơ bản.', 4);

-- --------------------------------------------------------
-- Dữ liệu mẫu cho bảng registrations - đăng ký hội nghị
-- --------------------------------------------------------
-- Đăng ký cho người dùng 1-3 (người tổ chức)
INSERT INTO `registrations` (`user_id`, `conference_id`, `status`, `registration_date`) VALUES
(5, 1, 'confirmed', '2025-06-01 08:30:00'),
(5, 2, 'confirmed', '2025-05-15 10:20:00'),
(6, 1, 'confirmed', '2025-06-02 14:45:00'),
(6, 3, 'confirmed', '2025-06-05 09:15:00'),
(7, 2, 'confirmed', '2025-05-20 11:30:00'),
(7, 4, 'confirmed', '2025-06-10 13:40:00'),
(8, 1, 'confirmed', '2025-06-03 15:50:00'),
(8, 5, 'confirmed', '2025-06-12 10:10:00'),
(9, 3, 'confirmed', '2025-06-07 16:25:00'),
(9, 4, 'confirmed', '2025-06-15 14:35:00');

-- --------------------------------------------------------
-- Dữ liệu mẫu cho bảng testimonials - đánh giá từ người dùng
-- --------------------------------------------------------
INSERT INTO `testimonials` (`user_id`, `conference_id`, `name`, `company`, `content`, `rating`, `is_featured`) VALUES
(5, 9, 'Phạm Văn Nam', 'Tech Company', 'Hội nghị Digital Transformation rất hữu ích cho công việc của tôi. Tôi học được nhiều chiến lược chuyển đổi số mà có thể áp dụng ngay vào doanh nghiệp.', 5, 1),
(6, 9, 'Vũ Thị Lan', 'Trường THPT Chu Văn An', 'Các buổi thảo luận về chuyển đổi số trong giáo dục đã cho tôi nhiều ý tưởng mới để áp dụng vào việc giảng dạy.', 4, 0),
(7, 9, 'Đỗ Hoàng Minh', 'Sinh viên ĐHBK', 'Được học hỏi từ các chuyên gia hàng đầu về công nghệ là trải nghiệm tuyệt vời. Hội nghị được tổ chức rất chuyên nghiệp.', 5, 1),
(8, 9, 'Hoàng Thị Mai', 'Bệnh viện Bạch Mai', 'Tôi đặc biệt ấn tượng với phần thảo luận về ứng dụng AI trong y tế. Rất thực tế và có thể áp dụng ngay.', 4, 0),
(9, 9, 'Trịnh Văn Hải', 'Freestyle Design', 'Hội nghị cung cấp những góc nhìn mới về thiết kế UX/UI trong kỷ nguyên chuyển đổi số. Tôi sẽ tham gia lần sau!', 5, 1);
