
// Hardcoded fake data for conferences and users

// Sample conferences data
const conferences = [
    {
        id: 1,
        title: "TechCrunch Disrupt 2024",
        description: "The world's leading startup event where groundbreaking companies launch, game-changing technologies debut, and industry-defining connections are made.",
        date: "2024-01-15",
        endDate: "2024-01-17",
        location: "San Francisco, CA",
        category: "Technology",
        price: 299.99,
        capacity: 5000,
        attendees: 4200,
        status: "active",
        image: "https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=400&fit=crop",
        organizer: {
            name: "TechCrunch",
            email: "events@techcrunch.com",
            phone: "+1 (555) 123-4567"
        },
        speakers: [
            { name: "Sarah Johnson", title: "CEO, InnovateTech", bio: "Leading expert in AI and machine learning" },
            { name: "Mike Chen", title: "CTO, StartupCorp", bio: "Pioneer in blockchain technology" },
            { name: "Emily Rodriguez", title: "Founder, GreenTech Solutions", bio: "Sustainability and clean energy advocate" }
        ],
        schedule: [
            { time: "09:00", title: "Registration & Networking", speaker: "" },
            { time: "10:00", title: "Keynote: The Future of AI", speaker: "Sarah Johnson" },
            { time: "11:30", title: "Panel: Blockchain Revolution", speaker: "Mike Chen" },
            { time: "13:00", title: "Lunch & Networking", speaker: "" },
            { time: "14:30", title: "Workshop: Green Technology", speaker: "Emily Rodriguez" }
        ]
    },
    {
        id: 2,
        title: "Design Systems Summit",
        description: "A comprehensive conference focused on building and scaling design systems across organizations of all sizes.",
        date: "2024-02-20",
        endDate: "2024-02-21",
        location: "New York, NY",
        category: "Design",
        price: 199.99,
        capacity: 1500,
        attendees: 1200,
        status: "active",
        image: "https://images.unsplash.com/photo-1475721027785-f74eccf877e2?w=800&h=400&fit=crop",
        organizer: {
            name: "Design Foundation",
            email: "hello@designfoundation.org",
            phone: "+1 (555) 987-6543"
        },
        speakers: [
            { name: "Alex Thompson", title: "Design Lead, Google", bio: "Expert in scalable design systems" },
            { name: "Lisa Park", title: "UX Director, Airbnb", bio: "Specialist in user-centered design" }
        ],
        schedule: [
            { time: "09:30", title: "Welcome & Introductions", speaker: "" },
            { time: "10:00", title: "Building Design Systems at Scale", speaker: "Alex Thompson" },
            { time: "11:30", title: "User Research in Design Systems", speaker: "Lisa Park" }
        ]
    },
    {
        id: 3,
        title: "Global Marketing Conference",
        description: "Connect with marketing leaders worldwide and discover the latest trends, strategies, and tools shaping the future of marketing.",
        date: "2024-03-10",
        endDate: "2024-03-12",
        location: "London, UK",
        category: "Marketing",
        price: 399.99,
        capacity: 3000,
        attendees: 2800,
        status: "active",
        image: "https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=800&h=400&fit=crop",
        organizer: {
            name: "Marketing Institute",
            email: "events@marketinginstitute.com",
            phone: "+44 20 1234 5678"
        },
        speakers: [
            { name: "David Wilson", title: "CMO, TechGlobal", bio: "Digital marketing transformation expert" },
            { name: "Maria Garcia", title: "VP Marketing, BrandCorp", bio: "Brand strategy and customer experience leader" }
        ],
        schedule: [
            { time: "09:00", title: "Registration", speaker: "" },
            { time: "10:00", title: "Digital Transformation in Marketing", speaker: "David Wilson" },
            { time: "12:00", title: "Building Authentic Brands", speaker: "Maria Garcia" }
        ]
    },
    {
        id: 4,
        title: "DevOps World Conference",
        description: "The premier event for DevOps professionals featuring the latest in automation, CI/CD, and cloud technologies.",
        date: "2024-04-05",
        endDate: "2024-04-07",
        location: "Berlin, Germany",
        category: "Technology",
        price: 449.99,
        capacity: 2500,
        attendees: 2100,
        status: "active",
        image: "https://images.unsplash.com/photo-1517077304055-6e89abbf09b0?w=800&h=400&fit=crop",
        organizer: {
            name: "DevOps Foundation",
            email: "conference@devops.org",
            phone: "+49 30 12345678"
        },
        speakers: [
            { name: "John Smith", title: "Principal Engineer, CloudTech", bio: "Cloud infrastructure and automation expert" },
            { name: "Jennifer Lee", title: "DevOps Manager, ScaleCorp", bio: "CI/CD and deployment pipeline specialist" }
        ],
        schedule: [
            { time: "09:00", title: "Welcome Keynote", speaker: "John Smith" },
            { time: "10:30", title: "Modern CI/CD Practices", speaker: "Jennifer Lee" },
            { time: "14:00", title: "Workshop: Kubernetes Deep Dive", speaker: "John Smith" }
        ]
    },
    {
        id: 5,
        title: "Business Innovation Summit",
        description: "Explore cutting-edge business strategies, innovation frameworks, and leadership insights from industry pioneers.",
        date: "2024-05-18",
        endDate: "2024-05-19",
        location: "Tokyo, Japan",
        category: "Business",
        price: 349.99,
        capacity: 2000,
        attendees: 1850,
        status: "active",
        image: "https://images.unsplash.com/photo-1556761175-4b46a572b786?w=800&h=400&fit=crop",
        organizer: {
            name: "Innovation Hub Asia",
            email: "events@innovationhub.asia",
            phone: "+81 3 1234 5678"
        },
        speakers: [
            { name: "Hiroshi Tanaka", title: "CEO, FutureCorp", bio: "Business transformation and innovation leader" },
            { name: "Rachel Kim", title: "Strategy Director, GlobalVentures", bio: "Strategic planning and market expansion expert" }
        ],
        schedule: [
            { time: "09:30", title: "Innovation in the Digital Age", speaker: "Hiroshi Tanaka" },
            { time: "11:00", title: "Global Market Strategies", speaker: "Rachel Kim" },
            { time: "15:30", title: "Panel: Future of Business", speaker: "Both speakers" }
        ]
    },
    {
        id: 6,
        title: "AI & Machine Learning Expo",
        description: "Discover the latest breakthroughs in artificial intelligence and machine learning from leading researchers and practitioners.",
        date: "2024-06-22",
        endDate: "2024-06-24",
        location: "San Francisco, CA",
        category: "Technology",
        price: 599.99,
        capacity: 4000,
        attendees: 3500,
        status: "active",
        image: "https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=800&h=400&fit=crop",
        organizer: {
            name: "AI Research Institute",
            email: "expo@airesearch.org",
            phone: "+1 (555) 456-7890"
        },
        speakers: [
            { name: "Dr. Amanda Foster", title: "AI Research Director, TechLab", bio: "Pioneer in neural network architectures" },
            { name: "Robert Zhang", title: "ML Engineer, DataCorp", bio: "Expert in large-scale machine learning systems" }
        ],
        schedule: [
            { time: "09:00", title: "The State of AI", speaker: "Dr. Amanda Foster" },
            { time: "11:00", title: "Scaling ML in Production", speaker: "Robert Zhang" },
            { time: "14:00", title: "Hands-on: Building Neural Networks", speaker: "Dr. Amanda Foster" }
        ]
    }
];

// Sample user data
const currentUser = {
    id: 1,
    name: "John Doe",
    email: "john.doe@example.com",
    title: "Senior Software Engineer",
    location: "San Francisco, CA",
    bio: "Passionate software engineer with 8+ years of experience in full-stack development. Love attending tech conferences and sharing knowledge with the community.",
    avatar: "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop&crop=face",
    joinedConferences: [1, 2, 4, 6], // Conference IDs the user has joined
    projects: [
        {
            id: 1,
            title: "Conference Management System",
            description: "A full-stack web application for managing tech conferences with React and Node.js",
            technologies: ["React", "Node.js", "MongoDB", "Express"],
            github: "https://github.com/johndoe/conference-system",
            demo: "https://conference-demo.com",
            image: "https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=400&h=200&fit=crop"
        },
        {
            id: 2,
            title: "Task Management Tool",
            description: "A collaborative task management application with real-time updates and team features",
            technologies: ["Vue.js", "Firebase", "Tailwind CSS"],
            github: "https://github.com/johndoe/task-manager",
            demo: "https://taskmanager-demo.com",
            image: "https://images.unsplash.com/photo-1611224923853-80b023f02d71?w=400&h=200&fit=crop"
        },
        {
            id: 3,
            title: "E-learning Platform",
            description: "An online learning platform with video streaming, quizzes, and progress tracking",
            technologies: ["Angular", "Python", "PostgreSQL", "AWS"],
            github: "https://github.com/johndoe/elearning",
            demo: "https://elearning-demo.com",
            image: "https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=400&h=200&fit=crop"
        }
    ],
    stats: {
        conferencesJoined: 5,
        projectsCount: 3,
        connections: 42
    }
};

// Helper functions to simulate API calls
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

function getCurrentUser() {
    return currentUser;
}

function getUserJoinedConferences() {
    return conferences.filter(conf => currentUser.joinedConferences.includes(conf.id));
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

// Admin functions
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
