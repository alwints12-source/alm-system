# 🏢 Apartment Management System

A MERN-stack web application that helps residential apartment associations manage tenants, flats, maintenance, and billing.

---

## 🚀 Features
- **User Authentication & Authorization** (JWT, protected routes, role-based access)
- **Tenant Management** (add, update, delete tenants)
- **Flat Management** (track apartment flats and occupancy)
- **Maintenance Requests** (submit, view, and resolve issues)
- **Billing System** (generate and manage bills for tenants)
- **Secure Login/Register** with password hashing

---

## 📂 Project Structure
```
apartment_management_system/
├── backend/        # Express.js, MongoDB, APIs
│   ├── config/     # Database connection
│   ├── middleware/ # Auth middleware
│   ├── models/     # Mongoose schemas
│   ├── routes/     # API routes
│   └── server.js   # Express app entrypoint
├── frontend/       # React.js, Context API, Pages
│   ├── src/
│   │   ├── components/
│   │   ├── context/
│   │   ├── pages/
│   │   └── App.js
└── README.md
```

---

## 🛠️ Installation & Setup

### 1. Clone the repo
```bash
git clone https://github.com/alwints12-source/Apartment-Manangement-Systems.git
cd apartment_management_system
```

### 2. Install dependencies
```bash
cd backend && npm install
cd ../frontend && npm install
```

### 3. Setup environment
In **backend/.env**:
```
MONGO_URI=mongodb+srv://<your-cluster>
JWT_SECRET=your_jwt_secret
PORT=5001
```

### 4. Run the app
Backend:
```bash
cd backend
npm run dev
```

Frontend:
```bash
cd frontend
npm start
```

---

## 🧪 Jira Workflow
- **Epic: AMS-1 User Authentication & Authorization**
- Stories: AMS-2 Register, AMS-3 Login, AMS-4 Role-Based Access
- Subtasks: AMS-5..AMS-10 etc.
- Feature branches mapped to Jira tickets (e.g. `feature/AMS-2-register`).

---

## 📌 Branching Strategy
- `main` → stable production-ready code
- `feature/*` → one branch per Jira ticket
- Use PRs with descriptive commit messages (`AMS-5: backend register API`)

---

## 📸 Screenshots
(Add images of login page, dashboard, etc.)

---

## 👨‍💻 Tech Stack
- **Frontend:** React.js, Context API, Axios
- **Backend:** Node.js, Express.js, JWT, Mongoose
- **Database:** MongoDB
- **Dev Tools:** GitHub, Jira, CI/CD (to be integrated)

---

## 🤝 Contributing
1. Create a feature branch (`git checkout -b feature/AMS-XYZ`)
2. Commit changes (`git commit -m "AMS-XYZ: description"`)
3. Push branch (`git push origin feature/AMS-XYZ`)
4. Open a Pull Request

---

## 📜 License
MIT License © 2025
