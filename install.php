<?php
session_start();

if (file_exists(__DIR__ . '/install.lock')) {
    header('Location: index.php');
    exit();
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

function phpExtensions()
{
    $required = ['pdo', 'pdo_mysql', 'mysqli', 'mbstring'];
    $optional = ['gd'];
    $result = ['required' => [], 'optional' => []];
    foreach ($required as $ext) {
        $result['required'][$ext] = extension_loaded($ext);
    }
    foreach ($optional as $ext) {
        $result['optional'][$ext] = extension_loaded($ext);
    }
    return $result;
}

function dirWritable($dir)
{
    $testFile = $dir . '/_test_write.tmp';
    $writable = @file_put_contents($testFile, 'test') !== false;
    @unlink($testFile);
    return $writable;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 2) {
        $host = trim($_POST['db_host']);
        $user = trim($_POST['db_user']);
        $pass = trim($_POST['db_pass']);
        $dbname = trim($_POST['db_name']);

        if (empty($host) || empty($user) || empty($dbname)) {
            $error = 'Please fill in all required database fields.';
        } else {
            $_SESSION['install_db_host'] = $host;
            $_SESSION['install_db_user'] = $user;
            $_SESSION['install_db_pass'] = $pass;
            $_SESSION['install_db_name'] = $dbname;

            try {
                $pdo = new PDO("mysql:host=$host;charset=utf8", $user, $pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8 COLLATE utf8_general_ci");
                $pdo->exec("USE `$dbname`");

                $sql = file_get_contents(__DIR__ . '/database/canteen.sql');
                $statements = explode(';', $sql);
                foreach ($statements as $stmt) {
                    $stmt = trim($stmt);
                    if (!empty($stmt)) {
                        $pdo->exec($stmt);
                    }
                }

                $config = "<?php\n";
                $config .= "\$host = '$host';\n";
                $config .= "\$dbname = '$dbname';\n";
                $config .= "\$username = '$user';\n";
                $config .= "\$password = '" . addslashes($pass) . "';\n\n";
                $config .= "try {\n";
                $config .= "    \$pdo = new PDO(\"mysql:host=\$host;dbname=\$dbname;charset=utf8\", \$username, \$password);\n";
                $config .= "    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);\n";
                $config .= "    \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);\n";
                $config .= "} catch (PDOException \$e) {\n";
                $config .= "    die(\"Connection failed: \" . \$e->getMessage());\n";
                $config .= "}\n";

                file_put_contents(__DIR__ . '/config/db.php', $config);

                file_put_contents(__DIR__ . '/install.lock', 'Installed on: ' . date('Y-m-d H:i:s'));
                $success = 'Installation completed successfully!';
                $step = 3;
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    }
}

$extensions = phpExtensions();
$allRequiredOk = !in_array(false, $extensions['required'], true);
$configDirWritable = dirWritable(__DIR__ . '/config');
$sqlFileExists = file_exists(__DIR__ . '/database/canteen.sql');
$phpVersionOk = version_compare(PHP_VERSION, '7.4', '>=');
$canProceed = $allRequiredOk && $configDirWritable && $sqlFileExists && $phpVersionOk;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canteen System - Installer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .installer-card {
            max-width: 700px;
            width: 100%;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .installer-header {
            background: linear-gradient(135deg, #2d3436, #636e72);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .installer-header h1 {
            font-size: 2rem;
            margin-bottom: 5px;
        }
        .installer-body {
            background: white;
            padding: 35px;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
        }
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
        }
        .step-circle.active {
            background: #007bff;
            color: white;
        }
        .step-circle.done {
            background: #28a745;
            color: white;
        }
        .step-circle.inactive {
            background: #e9ecef;
            color: #6c757d;
        }
        .step-line {
            width: 60px;
            height: 3px;
            align-self: center;
            background: #e9ecef;
        }
        .step-line.done {
            background: #28a745;
        }
        .req-pass {
            color: #28a745;
        }
        .req-fail {
            color: #dc3545;
        }
        .btn-install {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 40px;
            font-size: 18px;
            border-radius: 50px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .btn-install:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .success-icon {
            font-size: 80px;
            color: #28a745;
        }
        .cred-box {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="installer-card">
        <div class="installer-header">
            <h1><i class="fas fa-utensils"></i> Canteen System</h1>
            <p class="mb-0">Installation Wizard</p>
        </div>
        <div class="installer-body">
            <div class="step-indicator">
                <div class="step-circle <?php echo $step >= 1 ? ($step > 1 ? 'done' : 'active') : 'inactive'; ?>">
                    <?php echo $step > 1 ? '<i class="fas fa-check"></i>' : '1'; ?>
                </div>
                <div class="step-line <?php echo $step > 1 ? 'done' : ''; ?>"></div>
                <div class="step-circle <?php echo $step >= 2 ? ($step > 2 ? 'done' : 'active') : 'inactive'; ?>">
                    <?php echo $step > 2 ? '<i class="fas fa-check"></i>' : '2'; ?>
                </div>
                <div class="step-line <?php echo $step > 2 ? 'done' : ''; ?>"></div>
                <div class="step-circle <?php echo $step >= 3 ? ($step > 3 ? 'done' : 'active') : 'inactive'; ?>">
                    3
                </div>
            </div>

            <?php if ($step === 1): ?>
                <h4 class="mb-4"><i class="fas fa-clipboard-check"></i> System Requirements</h4>
                <table class="table table-bordered">
                    <tr>
                        <td>PHP Version (>= 7.4)</td>
                        <td><?php echo $phpVersionOk ? '<span class="req-pass"><i class="fas fa-check-circle"></i> ' . PHP_VERSION . '</span>' : '<span class="req-fail"><i class="fas fa-times-circle"></i> ' . PHP_VERSION . '</span>'; ?></td>
                    </tr>
                    <?php foreach ($extensions['required'] as $ext => $loaded): ?>
                        <tr>
                            <td>PHP Extension: <code><?php echo $ext; ?></code> <span class="badge bg-danger">Required</span></td>
                            <td><?php echo $loaded ? '<span class="req-pass"><i class="fas fa-check-circle"></i> Loaded</span>' : '<span class="req-fail"><i class="fas fa-times-circle"></i> Missing</span>'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php foreach ($extensions['optional'] as $ext => $loaded): ?>
                        <tr>
                            <td>PHP Extension: <code><?php echo $ext; ?></code> <span class="badge bg-secondary">Optional</span></td>
                            <td><?php echo $loaded ? '<span class="req-pass"><i class="fas fa-check-circle"></i> Loaded</span>' : '<span class="req-warn"><i class="fas fa-exclamation-triangle" style="color:#ffc107;"></i> Not installed (image uploads still work)</span>'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td>Config directory writable</td>
                        <td><?php echo $configDirWritable ? '<span class="req-pass"><i class="fas fa-check-circle"></i> Writable</span>' : '<span class="req-fail"><i class="fas fa-times-circle"></i> Not writable</span>'; ?></td>
                    </tr>
                    <tr>
                        <td>Database SQL file</td>
                        <td><?php echo $sqlFileExists ? '<span class="req-pass"><i class="fas fa-check-circle"></i> Found</span>' : '<span class="req-fail"><i class="fas fa-times-circle"></i> Missing</span>'; ?></td>
                    </tr>
                </table>

                <div class="text-muted small mb-3">
                    <i class="fas fa-info-circle"></i>
                    To enable GD in XAMPP: open <code>php.ini</code> and uncomment <code>extension=gd</code>.
                </div>

                <?php if ($canProceed): ?>
                    <div class="text-center mt-4">
                        <a href="install.php?step=2" class="btn btn-install">
                            <i class="fas fa-arrow-right"></i> Continue to Database Setup
                        </a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger text-center">
                        <i class="fas fa-exclamation-triangle"></i> Please fix the requirements above before proceeding.
                    </div>
                <?php endif; ?>

            <?php elseif ($step === 2): ?>
                <h4 class="mb-4"><i class="fas fa-database"></i> Database Configuration</h4>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Database Host</label>
                        <input type="text" name="db_host" class="form-control" value="<?php echo htmlspecialchars($_SESSION['install_db_host'] ?? 'localhost'); ?>" required>
                        <small class="text-muted">Usually <code>localhost</code></small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Database Username</label>
                            <input type="text" name="db_user" class="form-control" value="<?php echo htmlspecialchars($_SESSION['install_db_user'] ?? 'root'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Database Password</label>
                            <input type="text" name="db_pass" class="form-control" value="<?php echo htmlspecialchars($_SESSION['install_db_pass'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Database Name</label>
                        <input type="text" name="db_name" class="form-control" value="<?php echo htmlspecialchars($_SESSION['install_db_name'] ?? 'canteen_db'); ?>" required>
                        <small class="text-muted">Will be created if it doesn't exist</small>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> The installer will create the database, import tables, insert sample data, and generate the configuration file.
                    </div>

                    <div class="text-center mt-4">
                        <a href="install.php?step=1" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                        <button type="submit" class="btn btn-install">
                            <i class="fas fa-rocket"></i> Install Now
                        </button>
                    </div>
                </form>

            <?php elseif ($step === 3): ?>
                <?php if ($success): ?>
                    <div class="text-center">
                        <div class="success-icon mb-3">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 class="text-success">Installation Complete!</h3>
                        <p class="text-muted">The canteen ordering system has been installed successfully.</p>

                        <div class="cred-box text-start mt-4">
                            <h6><i class="fas fa-key"></i> Admin Credentials</h6>
                            <p class="mb-1"><strong>Email:</strong> admin@canteen.com</p>
                            <p class="mb-0"><strong>Password:</strong> password</p>
                        </div>

                        <div class="mt-4">
                            <a href="admin/login.php" class="btn btn-dark me-2"><i class="fas fa-shield-alt"></i> Go to Admin Panel</a>
                            <a href="index.php" class="btn btn-primary"><i class="fas fa-home"></i> Go to Homepage</a>
                        </div>

                        <p class="text-muted small mt-4">
                            <i class="fas fa-exclamation-triangle"></i> For security, delete the <code>install.php</code> and <code>install.lock</code> files after installation.
                        </p>
                    </div>
                <?php else: ?>
                    <div class="text-center">
                        <h4 class="text-danger">Installation Failed</h4>
                        <p><?php echo htmlspecialchars($error); ?></p>
                        <a href="install.php?step=2" class="btn btn-primary">Try Again</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
