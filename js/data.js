// Dữ liệu mẫu cho hội nghị và người dùng

// Dữ liệu hội nghị mẫu
const conferences = [
    {        id: 1,
        title: "Vietnam Tech Summit 2025",
        description: "Sự kiện công nghệ hàng đầu Việt Nam quy tụ các công ty khởi nghiệp tiên phong, ra mắt công nghệ đột phá, và kết nối các chuyên gia trong ngành.",
        date: "2025-09-15",
        endDate: "2025-09-17",
        location: "TP. Hồ Chí Minh",
        category: "Công nghệ",
        price: 1999000,
        capacity: 3000,
        attendees: 2600,
        status: "active",
        isManaged: true,
        image: "https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=400&fit=crop",
        organizer: {
            name: "VnTech Media",
            email: "events@vntech.com.vn",
            phone: "+84 28 1234 5678"
        },        speakers: [
            { name: "Nguyễn Thị Minh", title: "CEO, InnovateTech Vietnam", bio: "Chuyên gia hàng đầu về AI và học máy" },
            { name: "Trần Đức Khải", title: "CTO, VietStartup", bio: "Tiên phong trong công nghệ blockchain" },
            { name: "Phạm Thị Hương", title: "Founder, GreenTech Solutions", bio: "Chuyên gia về phát triển bền vững và năng lượng sạch" },
            { name: "Lê Văn Bách", title: "AI Research Director, FPT Software", bio: "Tiến sĩ AI với hơn 15 năm kinh nghiệm nghiên cứu và ứng dụng thực tế" },
            { name: "Đinh Thu Trang", title: "Cloud Architect, AWS Vietnam", bio: "Chuyên gia giải pháp đám mây cho doanh nghiệp lớn" }
        ],
        schedule: [
            { 
                eventDate: "2024-05-15", 
                time: "08:30 - 09:30", 
                title: "Đăng ký & Kết nối", 
                speaker: "",
                description: "Đón tiếp và cung cấp thẻ hội nghị, tài liệu, thời gian kết nối với các đồng nghiệp"
            },
            { 
                eventDate: "2024-05-15", 
                time: "09:30 - 10:00", 
                title: "Khai mạc Hội nghị", 
                speaker: "Ban Tổ chức",
                description: "Phát biểu khai mạc, giới thiệu chương trình và các diễn giả chính"
            },
            { 
                eventDate: "2024-05-15", 
                time: "10:00 - 11:30", 
                title: "Keynote: Tương lai của AI trong doanh nghiệp Việt Nam", 
                speaker: "Nguyễn Thị Minh",
                description: "Phân tích xu hướng AI toàn cầu và cơ hội áp dụng tại Việt Nam, các trường hợp thành công điển hình"
            },
            { 
                eventDate: "2024-05-15", 
                time: "11:30 - 12:30", 
                title: "Tọa đàm: Cuộc cách mạng Blockchain", 
                speaker: "Trần Đức Khải",
                description: "Tổng quan về tiến bộ mới nhất trong công nghệ blockchain và ứng dụng thực tế trong ngành tài chính, chuỗi cung ứng"
            },
            { 
                eventDate: "2024-05-15", 
                time: "12:30 - 14:00", 
                title: "Ăn trưa & Kết nối", 
                speaker: "",
                description: "Buffet trưa và cơ hội giao lưu, kết nối với các chuyên gia, diễn giả"
            },
            { 
                eventDate: "2024-05-15", 
                time: "14:00 - 15:30", 
                title: "Workshop: Công nghệ xanh và Phát triển bền vững", 
                speaker: "Phạm Thị Hương",
                description: "Giới thiệu các giải pháp công nghệ xanh giúp doanh nghiệp tiết kiệm năng lượng và giảm tác động môi trường"
            },
            { 
                eventDate: "2024-05-16", 
                time: "09:00 - 10:30", 
                title: "Deep Dive: Kiến trúc AI hiện đại", 
                speaker: "Lê Văn Bách",
                description: "Phân tích chuyên sâu về các mô hình AI tiên tiến và cách triển khai trong môi trường sản xuất"
            },
            { 
                eventDate: "2024-05-16", 
                time: "10:45 - 12:15", 
                title: "Workshop: Cloud Migration Strategy", 
                speaker: "Đinh Thu Trang",
                description: "Hướng dẫn chiến lược chuyển đổi hạ tầng lên đám mây an toàn và hiệu quả"
            },
            { 
                eventDate: "2024-05-16", 
                time: "12:15 - 13:30", 
                title: "Ăn trưa & Demo", 
                speaker: "",
                description: "Bữa trưa kết hợp với trình diễn công nghệ mới từ các đối tác"
            },
            { 
                eventDate: "2024-05-16", 
                time: "13:30 - 15:00", 
                title: "Tọa đàm: Khởi nghiệp công nghệ tại Việt Nam", 
                speaker: "Trần Đức Khải, Nguyễn Thị Minh",
                description: "Thảo luận về thách thức và cơ hội cho các startup công nghệ tại Việt Nam, chia sẻ kinh nghiệm thực tế"
            },
            { 
                eventDate: "2024-05-17", 
                time: "09:00 - 10:30", 
                title: "Workshop: Data Science trong thực tiễn", 
                speaker: "Lê Văn Bách",
                description: "Hướng dẫn thực hành về phân tích dữ liệu và xây dựng mô hình dự đoán"
            },
            { 
                eventDate: "2024-05-17", 
                time: "10:45 - 12:00", 
                title: "Panel: Tương lai công nghệ Việt Nam", 
                speaker: "Nhiều diễn giả",
                description: "Thảo luận về xu hướng công nghệ và cơ hội phát triển cho ngành CNTT Việt Nam trong 5 năm tới"
            },
            { 
                eventDate: "2024-05-17", 
                time: "12:00 - 13:00", 
                title: "Ăn trưa", 
                speaker: "",
                description: ""
            },
            { 
                eventDate: "2024-05-17", 
                time: "13:00 - 14:30", 
                title: "Trao giải Hackathon & Demo Day", 
                speaker: "Ban Tổ chức",
                description: "Trình diễn các sản phẩm từ cuộc thi Hackathon và trao giải cho các đội xuất sắc"
            },
            { 
                eventDate: "2024-05-17", 
                time: "14:30 - 15:30", 
                title: "Tổng kết & Networking", 
                speaker: "Ban Tổ chức",
                description: "Phát biểu bế mạc, tổng kết hội nghị và thời gian kết nối cuối cùng"
            }
        ],
        attendeesData: [
            { id: 1, name: 'Lê Thị An', email: 'an.le@email.com', registrationDate: '2024-05-01', status: 'confirmed' },
            { id: 2, name: 'Nguyễn Văn Bình', email: 'binh.nguyen@email.com', registrationDate: '2024-05-03', status: 'registered' },
            { id: 3, name: 'Trần Thu Cúc', email: 'cuc.tran@email.com', registrationDate: '2024-05-05', status: 'attended' }
        ]
    },
    {        id: 2,
        title: "Hội thảo Thiết kế UX/UI 2025",
        description: "Hội thảo chuyên sâu tập trung vào xây dựng và phát triển hệ thống thiết kế cho tổ chức thuộc mọi quy mô.",
        date: "2025-08-20",
        endDate: "2025-08-21",
        location: "Hà Nội",
        category: "Thiết kế",
        price: 1500000,
        capacity: 800,
        attendees: 650,
        status: "active",
        isManaged: false,
        image: "https://images.unsplash.com/photo-1475721027785-f74eccf877e2?w=800&h=400&fit=crop",
        organizer: {
            name: "Vietnam Design Association",
            email: "contact@vndesign.org.vn",
            phone: "+84 24 3456 7890"
        },        speakers: [
            { name: "Đặng Minh Tuấn", title: "Design Lead, Google Vietnam", bio: "Chuyên gia về hệ thống thiết kế quy mô lớn" },
            { name: "Nguyễn Thảo Linh", title: "UX Director, Tiki", bio: "Chuyên gia thiết kế lấy người dùng làm trung tâm" },
            { name: "Trần Minh Hiếu", title: "UI/UX Consultant, Independent", bio: "Chuyên gia tư vấn với hơn 50 dự án thiết kế trải nghiệm người dùng thành công" }
        ],
        schedule: [
            { 
                eventDate: "2024-06-20", 
                time: "09:00 - 09:30", 
                title: "Đón tiếp & Đăng ký", 
                speaker: "",
                description: "Check-in và nhận tài liệu hội thảo"
            },
            { 
                eventDate: "2024-06-20", 
                time: "09:30 - 10:00", 
                title: "Chào mừng & Giới thiệu", 
                speaker: "Ban Tổ chức",
                description: "Giới thiệu chương trình và các diễn giả, mục tiêu của hội thảo"
            },
            { 
                eventDate: "2024-06-20", 
                time: "10:00 - 11:30", 
                title: "Xây dựng Hệ thống Thiết kế Quy mô lớn", 
                speaker: "Đặng Minh Tuấn",
                description: "Chiến lược xây dựng design system cho doanh nghiệp quy mô lớn, các thách thức và giải pháp"
            },
            { 
                eventDate: "2024-06-20", 
                time: "11:30 - 12:30", 
                title: "Nghiên cứu Người dùng trong Hệ thống Thiết kế", 
                speaker: "Nguyễn Thảo Linh",
                description: "Phương pháp nghiên cứu và tích hợp phản hồi từ người dùng vào quy trình thiết kế"
            },
            { 
                eventDate: "2024-06-20", 
                time: "12:30 - 14:00", 
                title: "Ăn trưa & Networking", 
                speaker: "",
                description: "Thời gian giao lưu và kết nối với các chuyên gia"
            },
            { 
                eventDate: "2024-06-20", 
                time: "14:00 - 15:30", 
                title: "Workshop: Figma cho Design System", 
                speaker: "Đặng Minh Tuấn",
                description: "Hướng dẫn thực hành xây dựng design system sử dụng Figma, quản lý components và variants"
            },
            { 
                eventDate: "2024-06-20", 
                time: "15:45 - 17:00", 
                title: "Panel: Tương lai của Design System", 
                speaker: "Tất cả diễn giả",
                description: "Thảo luận về xu hướng và tương lai của hệ thống thiết kế trong các tổ chức"
            },
            { 
                eventDate: "2024-06-21", 
                time: "09:30 - 11:00", 
                title: "Thiết kế Giao diện Đa nền tảng", 
                speaker: "Trần Minh Hiếu",
                description: "Chiến lược thiết kế responsive và adaptive cho web, mobile và các thiết bị khác"
            },
            { 
                eventDate: "2024-06-21", 
                time: "11:15 - 12:30", 
                title: "Case Study: Redesign Tiki's Design System", 
                speaker: "Nguyễn Thảo Linh",
                description: "Bài học từ quá trình cải tiến design system của Tiki, thách thức và kết quả"
            },
            { 
                eventDate: "2024-06-21", 
                time: "12:30 - 13:30", 
                title: "Ăn trưa", 
                speaker: "",
                description: ""
            },
            { 
                eventDate: "2024-06-21", 
                time: "13:30 - 15:30", 
                title: "Workshop: Triển khai Design System vào Code", 
                speaker: "Đặng Minh Tuấn, Trần Minh Hiếu",
                description: "Hướng dẫn thực hành chuyển thiết kế thành component code (React, CSS-in-JS)"
            },
            { 
                eventDate: "2024-06-21", 
                time: "15:45 - 16:30", 
                title: "Tổng kết và Câu hỏi", 
                speaker: "Ban Tổ chức",
                description: "Tổng kết hội thảo, trả lời câu hỏi từ người tham dự"
            }
        ],
        attendeesData: [
            { id: 1, name: 'Vũ Đình Đức', email: 'duc.vu@email.com', registrationDate: '2024-06-01', status: 'confirmed' },
            { id: 2, name: 'Hoàng Thanh Hương', email: 'huong.hoang@email.com', registrationDate: '2024-06-03', status: 'registered' }
        ]
    },
    {        id: 3,
        title: "Hội nghị Marketing Toàn quốc 2025",
        description: "Kết nối với các nhà lãnh đạo marketing toàn quốc và khám phá xu hướng, chiến lược và công cụ mới nhất định hình tương lai của ngành marketing.",
        date: "2025-10-10",
        endDate: "2025-10-12",
        location: "Đà Nẵng",
        category: "Marketing",
        price: 2500000,
        capacity: 1200,
        attendees: 1050,
        status: "active",
        isManaged: true,
        image: "https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=800&h=400&fit=crop",
        organizer: {
            name: "Viện Marketing Việt Nam",
            email: "events@vmarketing.org.vn",
            phone: "+84 236 789 1234"
        },
        speakers: [
            { name: "Nguyễn Đức Thịnh", title: "CMO, FPT", bio: "Chuyên gia chuyển đổi marketing số" },
            { name: "Trần Mỹ Linh", title: "VP Marketing, Vinamilk", bio: "Chuyên gia chiến lược thương hiệu và trải nghiệm khách hàng" }
        ],
        schedule: [
            { time: "09:00", title: "Đăng ký", speaker: "" },
            { time: "10:00", title: "Chuyển đổi Số trong Marketing", speaker: "Nguyễn Đức Thịnh" },
            { time: "12:00", title: "Xây dựng Thương hiệu Bền vững", speaker: "Trần Mỹ Linh" }
        ],
        attendeesData: []
    },
    {
        id: 4,
        title: "DevOps Vietnam Conference",
        description: "Sự kiện hàng đầu dành cho chuyên gia DevOps, giới thiệu các công nghệ mới nhất về tự động hóa, CI/CD và điện toán đám mây.",
        date: "2024-08-05",
        endDate: "2024-08-07",
        location: "Hà Nội",
        category: "Công nghệ",
        price: 2200000,
        capacity: 1000,
        attendees: 850,
        status: "active",
        isManaged: false,
        image: "https://images.unsplash.com/photo-1517077304055-6e89abbf09b0?w=800&h=400&fit=crop",
        organizer: {
            name: "Vietnam DevOps Community",
            email: "conference@vdevops.vn",
            phone: "+84 24 9876 5432"
        },
        speakers: [
            { name: "Lê Văn Hải", title: "Principal Engineer, VNG Cloud", bio: "Chuyên gia về hạ tầng đám mây và tự động hóa" },
            { name: "Nguyễn Thị Mai", title: "DevOps Manager, FPT Software", bio: "Chuyên gia về CI/CD và quy trình triển khai" }
        ],
        schedule: [
            { time: "09:00", title: "Phát biểu Khai mạc", speaker: "Lê Văn Hải" },
            { time: "10:30", title: "CI/CD Hiện đại", speaker: "Nguyễn Thị Mai" },
            { time: "14:00", title: "Workshop: Kubernetes Chuyên sâu", speaker: "Lê Văn Hải" }
        ],
        attendeesData: []
    },
    {
        id: 5,
        title: "Vietnam Business Innovation Summit",
        description: "Khám phá chiến lược kinh doanh tiên tiến, khung sáng tạo và các góc nhìn lãnh đạo từ những người tiên phong trong ngành.",
        date: "2024-09-18",
        endDate: "2024-09-19",
        location: "TP. Hồ Chí Minh",
        category: "Kinh doanh",
        price: 2100000,
        capacity: 1200,
        attendees: 1050,
        status: "active",
        isManaged: true,
        image: "https://images.unsplash.com/photo-1556761175-4b46a572b786?w=800&h=400&fit=crop",
        organizer: {
            name: "Vietnam Innovation Hub",
            email: "events@vninnovation.vn",
            phone: "+84 28 9876 5432"
        },
        speakers: [
            { name: "Trần Văn Minh", title: "CEO, MoMo", bio: "Chuyên gia chuyển đổi kinh doanh và đổi mới sáng tạo" },
            { name: "Lê Hồng Nhung", title: "Strategy Director, Vingroup", bio: "Chuyên gia lập kế hoạch chiến lược và mở rộng thị trường" }
        ],
        schedule: [
            { time: "09:30", title: "Đổi mới trong Thời đại Số", speaker: "Trần Văn Minh" },
            { time: "11:00", title: "Chiến lược Thị trường Toàn cầu", speaker: "Lê Hồng Nhung" },
            { time: "15:30", title: "Tọa đàm: Tương lai Kinh doanh", speaker: "Cả hai diễn giả" }
        ],
        attendeesData: []
    },
    {
        id: 6,
        title: "Vietnam AI & Machine Learning Expo",
        description: "Khám phá những đột phá mới nhất trong lĩnh vực trí tuệ nhân tạo và học máy từ các nhà nghiên cứu và chuyên gia hàng đầu.",
        date: "2024-10-22",
        endDate: "2024-10-24",
        location: "Đà Nẵng",
        category: "Công nghệ",
        price: 2800000,
        capacity: 2000,
        attendees: 1750,
        status: "active",
        image: "https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=800&h=400&fit=crop",
        organizer: {
            name: "Vietnam AI Association",
            email: "expo@vai.org.vn",
            phone: "+84 236 456 7890"
        },
        speakers: [
            { name: "TS. Phạm Minh Tuấn", title: "AI Research Director, VinAI", bio: "Tiên phong trong kiến trúc mạng neural" },
            { name: "Nguyễn Đức Thành", title: "ML Engineer, Zalo", bio: "Chuyên gia về hệ thống học máy quy mô lớn" }
        ],
        schedule: [
            { time: "09:00", title: "Hiện trạng AI tại Việt Nam", speaker: "TS. Phạm Minh Tuấn" },
            { time: "11:00", title: "Ứng dụng ML trong Sản xuất", speaker: "Nguyễn Đức Thành" },
            { time: "14:00", title: "Thực hành: Xây dựng Mạng Neural", speaker: "TS. Phạm Minh Tuấn" }
        ],
        attendeesData: []
    }
];

// Dữ liệu người dùng mẫu
const sampleUser = {
    id: 1,
    name: "Nguyễn Văn Nam",
    email: "vannam@example.com.vn",
    title: "Kỹ sư phần mềm cao cấp",
    location: "TP. Hồ Chí Minh",
    bio: "Kỹ sư phần mềm nhiệt huyết với hơn 8 năm kinh nghiệm phát triển full-stack. Đam mê tham dự các hội nghị công nghệ và chia sẻ kiến thức với cộng đồng.",
    avatar: "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop&crop=face",
    joinedConferences: [1, 2, 4, 6], // ID của các hội nghị người dùng đã tham gia
    projects: [
        {
            id: 1,
            title: "Hệ thống Quản lý Hội nghị",
            description: "Ứng dụng web full-stack để quản lý hội nghị công nghệ sử dụng React và Node.js",
            technologies: ["React", "Node.js", "MongoDB", "Express"],
            github: "https://github.com/nguyenvannam/conference-system",
            demo: "https://conference-demo.com.vn",
            image: "https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=400&h=200&fit=crop"
        },
        {
            id: 2,
            title: "Công cụ Quản lý Công việc",
            description: "Ứng dụng quản lý công việc cộng tác với cập nhật thời gian thực và tính năng nhóm",
            technologies: ["Vue.js", "Firebase", "Tailwind CSS"],
            github: "https://github.com/nguyenvannam/task-manager",
            demo: "https://taskmanager-demo.com.vn",
            image: "https://images.unsplash.com/photo-1611224923853-80b023f02d71?w=400&h=200&fit=crop"
        },
        {
            id: 3,
            title: "Nền tảng Học trực tuyến",
            description: "Nền tảng học tập trực tuyến với phát video, bài kiểm tra và theo dõi tiến độ",
            technologies: ["Angular", "Python", "PostgreSQL", "AWS"],
            github: "https://github.com/nguyenvannam/elearning",
            demo: "https://elearning-demo.com.vn",
            image: "https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=400&h=200&fit=crop"
        }
    ],
    stats: {
        conferencesJoined: 5,
        projectsCount: 3,
        connections: 42
    }
};

// Các hàm hỗ trợ để mô phỏng API calls
function getConferences() {
    return conferences;
}

function getUpcomingConferences(limit = 3) {
    const upcoming = conferences
        .filter(conf => new Date(conf.date) > new Date())
        .sort((a, b) => new Date(a.date) - new Date(b.date))
        .slice(0, limit);
    return upcoming;
}

function getConferenceById(id) {
    return conferences.find(conf => conf.id === parseInt(id));
}

function getSampleUser() {
    return sampleUser;
}

function getUserJoinedConferences() {
    // Use auth.js getCurrentUser if available, otherwise fall back to sample data
    const user = typeof window.getCurrentUser === 'function' ? window.getCurrentUser() : sampleUser;
    return conferences.filter(conf => user.joinedConferences && user.joinedConferences.includes(conf.id));
}

function searchConferences(query, category = '', location = '') {
    return conferences.filter(conf => {
        const matchesQuery = !query || 
            conf.title.toLowerCase().includes(query.toLowerCase()) ||
            conf.description.toLowerCase().includes(query.toLowerCase()) ||
            conf.location.toLowerCase().includes(query.toLowerCase());
        
        const matchesCategory = !category || conf.category === category;
        const matchesLocation = !location || conf.location.includes(location);
        
        return matchesQuery && matchesCategory && matchesLocation;
    });
}

// Hàm quản trị
function getAdminStats() {
    const totalAttendees = conferences.reduce((sum, conf) => sum + conf.attendees, 0);
    const totalRevenue = conferences.reduce((sum, conf) => sum + (conf.attendees * conf.price), 0);
    const activeConferences = conferences.filter(conf => conf.status === 'active').length;
    
    return {
        totalConferences: conferences.length,
        activeConferences: activeConferences,
        totalAttendees: totalAttendees,
        totalRevenue: totalRevenue
    };
}
