<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join the Tournament - UK E-Sports League</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #F8FAFC;
        }
        
        .register-container {
            min-height: calc(100vh - 200px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4rem 2rem;
        }

        .register-card {
            background: white;
            padding: 3rem;
            border-radius: 1.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            width: 100%;
            max-width: 500px;
            border: 1px solid rgba(59, 130, 246, 0.1);
        }

        .register-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .register-header h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2rem;
            color: #1E293B;
            margin-bottom: 0.5rem;
        }

        .register-header p {
            color: #64748B;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #475569;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid #CBD5E1;
            border-radius: 0.75rem;
            font-size: 1rem;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #2563EB, #1D4ED8);
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 1.125rem;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 1rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
        }

        .alert {
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.95rem;
        }

        .alert-success {
            background: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }

        .alert-error {
            background: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="index.html" class="logo">
                <i class="fas fa-gamepad"></i>
                <span>UK E-Sports League</span>
            </a>
            <ul class="nav-links">
                <li><a href="index.html">Home</a></li>
                <li><a href="search_form.php">Search</a></li>
                <li><a href="register_form.html" style="color: #EF4444;">Merchandise</a></li>
                <li><a href="register.php" class="active" style="color: #2563EB;">Join Now</a></li>
                <li><a href="admin_login.html">Admin</a></li>
            </ul>
        </div>
    </nav>

    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <div style="width: 60px; height: 60px; background: #EFF6FF; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: #2563EB; font-size: 1.75rem;">
                    <i class="fas fa-trophy"></i>
                </div>
                <h1>Player Registration</h1>
                <p>Enter your details to join the UK E-Sports League tournament database.</p>
            </div>

            <?php
            if (isset($_GET['success'])) {
                echo '<div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>Registration Successful!</strong><br>
                            You have been added to the player database. Good luck!
                        </div>
                      </div>';
            } elseif (isset($_GET['error'])) {
                echo '<div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        ' . htmlspecialchars($_GET['error']) . '
                      </div>';
            }
            ?>

            <form action="process_register.php" method="POST">
                <div class="form-group">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" placeholder="Enter your first name" required>
                </div>

                <div class="form-group">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Enter your surname" required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" required>
                    <p style="font-size: 0.85rem; color: #64748B; margin-top: 0.5rem;"><i class="fas fa-lock"></i> Your email will be used for tournament updates.</p>
                </div>

                <button type="submit" class="btn-submit">
                    Register Now <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
                </button>
                
                <p style="text-align: center; margin-top: 1.5rem; color: #64748B; font-size: 0.9rem;">
                    Already registered? <a href="search_form.php" style="color: #2563EB; text-decoration: none; font-weight: 600;">Check your valid profile</a>
                </p>
            </form>
        </div>
    </div>

    <!-- Simple Footer -->
    <div style="text-align: center; padding: 2rem; color: #94A3B8; font-size: 0.9rem;">
        &copy; 2026 UK E-Sports League. All rights reserved.
    </div>

</body>
</html>