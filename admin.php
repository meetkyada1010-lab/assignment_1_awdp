<?php
session_start();
include 'config.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT id, username, password FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $isLoggedIn = true;
    } else {
        $loginError = "Invalid username or password";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit();
}

// Handle contact actions
if ($isLoggedIn && isset($_POST['action'])) {
    if ($_POST['action'] == 'mark_read' && isset($_POST['contact_id'])) {
        $stmt = $pdo->prepare("UPDATE contacts SET status = 'read' WHERE id = ?");
        $stmt->execute([$_POST['contact_id']]);
    } elseif ($_POST['action'] == 'delete_contact' && isset($_POST['contact_id'])) {
        $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
        $stmt->execute([$_POST['contact_id']]);
    }
}

// Get contacts if logged in
if ($isLoggedIn) {
    $contacts = $pdo->query("SELECT * FROM contacts ORDER BY created_at DESC")->fetchAll();
    $contact_stats = $pdo->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_messages,
        SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_messages
        FROM contacts")->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Portfolio</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --dark-bg: #1a1a2e;
            --light-bg: #16213e;
            --text-light: #eee;
        }

        body {
            background: var(--dark-bg);
            color: var(--text-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .admin-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card, .admin-card {
            background: var(--light-bg);
            border-radius: 15px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-light);
            border-radius: 8px;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--primary-color);
            color: var(--text-light);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 25px;
            padding: 10px 25px;
        }

        .table-dark {
            background: var(--light-bg);
        }

        .badge {
            padding: 0.5em 0.8em;
        }

        .stats-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .stats-card i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php if (!$isLoggedIn): ?>
        <!-- Login Form -->
        <div class="login-container">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-4">
                        <div class="login-card">
                            <div class="text-center mb-4">
                                <h2><i class="fas fa-user-shield"></i> Admin Login</h2>
                                <p class="text-muted">Access your portfolio dashboard</p>
                            </div>
                            
                            <?php if (isset($loginError)): ?>
                                <div class="alert alert-danger"><?php echo $loginError; ?></div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control" name="username" required>
                                    <small class="text-muted">Default: admin</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" class="form-control" name="password" required>
                                    <small class="text-muted">Default: admin123</small>
                                </div>
                                <button type="submit" name="login" class="btn btn-primary w-100">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                            </form>
                            
                            <div class="text-center mt-4">
                                <a href="index.php" class="text-decoration-none">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Website
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Admin Dashboard -->
        <div class="admin-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col">
                        <h1><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h1>
                        <p class="mb-0">Welcome back, <?php echo $_SESSION['admin_username']; ?>!</p>
                    </div>
                    <div class="col-auto">
                        <a href="index.php" class="btn btn-light me-2">
                            <i class="fas fa-eye me-2"></i>View Website
                        </a>
                        <a href="?logout=1" class="btn btn-outline-light">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <!-- Statistics -->
            <div class="row g-4 mb-5">
                <div class="col-lg-3 col-md-6">
                    <div class="stats-card">
                        <i class="fas fa-envelope text-primary"></i>
                        <h3><?php echo $contact_stats['total']; ?></h3>
                        <p>Total Messages</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stats-card">
                        <i class="fas fa-envelope-open text-warning"></i>
                        <h3><?php echo $contact_stats['new_messages']; ?></h3>
                        <p>New Messages</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stats-card">
                        <i class="fas fa-check-circle text-success"></i>
                        <h3><?php echo $contact_stats['read_messages']; ?></h3>
                        <p>Read Messages</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stats-card">
                        <i class="fas fa-calendar text-info"></i>
                        <h3><?php echo date('d'); ?></h3>
                        <p><?php echo date('M Y'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Contact Messages -->
            <div class="admin-card">
                <h3 class="mb-4"><i class="fas fa-envelope me-2"></i>Contact Messages</h3>
                
                <?php if (empty($contacts)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5>No messages yet</h5>
                        <p class="text-muted">Contact form submissions will appear here.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contacts as $contact): ?>
                                <tr>
                                    <td><?php echo $contact['id']; ?></td>
                                    <td><?php echo htmlspecialchars($contact['name']); ?></td>
                                    <td><?php echo htmlspecialchars($contact['email']); ?></td>
                                    <td><?php echo htmlspecialchars($contact['subject']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($contact['created_at'])); ?></td>
                                    <td>
                                        <?php if ($contact['status'] == 'new'): ?>
                                            <span class="badge bg-warning">New</span>
                                        <?php elseif ($contact['status'] == 'read'): ?>
                                            <span class="badge bg-success">Read</span>
                                        <?php else: ?>
                                            <span class="badge bg-info">Replied</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewMessage(<?php echo $contact['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($contact['status'] == 'new'): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="mark_read">
                                            <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                            <input type="hidden" name="action" value="delete_contact">
                                            <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Message Modal -->
        <div class="modal fade" id="messageModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content bg-dark">
                    <div class="modal-header">
                        <h5 class="modal-title">Message Details</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="messageContent">
                        <!-- Message content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewMessage(id) {
            // Find the contact data
            const contacts = <?php echo $isLoggedIn ? json_encode($contacts) : '[]'; ?>;
            const contact = contacts.find(c => c.id == id);
            
            if (contact) {
                const content = `
                    <div class="row">
                        <div class="col-md-6"><strong>Name:</strong> ${contact.name}</div>
                        <div class="col-md-6"><strong>Email:</strong> ${contact.email}</div>
                        <div class="col-md-6"><strong>Subject:</strong> ${contact.subject}</div>
                        <div class="col-md-6"><strong>Date:</strong> ${new Date(contact.created_at).toLocaleDateString()}</div>
                        <div class="col-12 mt-3"><strong>Message:</strong></div>
                        <div class="col-12 mt-2">
                            <div class="p-3" style="background: rgba(255,255,255,0.1); border-radius: 8px;">
                                ${contact.message}
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('messageContent').innerHTML = content;
                new bootstrap.Modal(document.getElementById('messageModal')).show();
            }
        }
    </script>
</body>
</html>