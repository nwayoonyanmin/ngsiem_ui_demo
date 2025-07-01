<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Kernellix SIEM Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@600&display=swap" rel="stylesheet">
  <style>
    body {
      background: url('https://www.kernellix.com/images/banner/banner-1.jpg') no-repeat center center fixed;
      background-size: cover;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .login-box {
      background: rgba(255, 255, 255, 0.95);
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.2);
      width: 100%;
      max-width: 400px;
      text-align: center;
    }
    .login-logo {
      max-width: 120px;
      margin-bottom: 10px;
    }
    .ngsiem-title {
      font-family: 'Titillium Web', sans-serif;
      font-weight: 900;
      font-size: 30px;
      color: #ba293a;
      margin-bottom: 18px;
    }
    .forgot-password {
      display: block;
      margin-top: 10px;
      font-size: 0.9rem;
    }
    .footer {
      margin-top: 20px;
      font-size: 0.85rem;
      opacity: 0.3;
      color: black;
    }
  </style>
</head>
<body>

<div class="login-box">
  <img src="https://media.licdn.com/dms/image/v2/C510BAQHJWLt6hIAlMw/company-logo_200_200/company-logo_200_200/0/1630633471811/kernellix_logo?e=2147483647&v=beta&t=u4Ov-Q36bYP6ElddkA2lUlfxYfaZ0aA0138y1qC157A" alt="Kernellix Logo" class="login-logo">
  <div class="ngsiem-title">NGSIEM</div>
  <form action="backend/authenticate.php" method="POST">
    <div class="mb-3 text-start">
      <label for="username" class="form-label">Username</label>
      <input type="text" name="username" id="username" class="form-control" required />
    </div>
    <div class="mb-4 text-start">
      <label for="password" class="form-label">Password</label>
      <input type="password" name="password" id="password" class="form-control" required />
    </div>
    <button type="submit" class="btn btn-primary w-100">Login</button>
    <div class="footer text-center">
        &copy; <?php echo date("Y"); ?> Kernellix. All rights reserved.
    </div>
  </form>
</div>



</body>
</html>
