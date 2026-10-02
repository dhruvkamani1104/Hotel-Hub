<?php
session_start();
include("../Config/connection.php");

if(!isset($_SESSION['username']))
{
    header("Location: login.php");
    exit();
}

$username=$_SESSION['username'];

$user=mysqli_query($conn,"SELECT * FROM users WHERE username='$username'");
$userData=mysqli_fetch_assoc($user);

$email=$userData['email'];

$customer=mysqli_query($conn,"SELECT * FROM customers WHERE email='$email'");
$customerData=mysqli_fetch_assoc($customer);

if(isset($_POST['update']))
{
    $first_name=mysqli_real_escape_string($conn,$_POST['first_name']);
    $last_name=mysqli_real_escape_string($conn,$_POST['last_name']);
    $gender=mysqli_real_escape_string($conn,$_POST['gender']);
    $phone=mysqli_real_escape_string($conn,$_POST['phone']);
    $address=mysqli_real_escape_string($conn,$_POST['address']);
    $id_proof=mysqli_real_escape_string($conn,$_POST['id_proof']);

    if($customerData)
    {
        mysqli_query($conn,"UPDATE customers SET
        first_name='$first_name',
        last_name='$last_name',
        gender='$gender',
        phone='$phone',
        address='$address',
        id_proof='$id_proof'
        WHERE email='$email'");
    }
    else
    {
        mysqli_query($conn,"INSERT INTO customers
        (first_name,last_name,gender,phone,email,address,id_proof)
        VALUES
        ('$first_name','$last_name','$gender','$phone','$email','$address','$id_proof')");
    }

    echo "<script>alert('Profile Updated Successfully');</script>";
    echo "<script>window.location='profile.php';</script>";
    exit();
}

$customer=mysqli_query($conn,"SELECT * FROM customers WHERE email='$email'");
$customerData=mysqli_fetch_assoc($customer);
?>

<!DOCTYPE html>
<html>
<head>

<title>My Profile</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f4f6f9;
}

.card{
margin-top:40px;
border:none;
border-radius:15px;
box-shadow:0 0 15px rgba(0,0,0,.15);
}

</style>

</head>

<body>

<div class="container">

<div class="card">

<div class="card-header bg-primary text-white">

<h3>My Profile</h3>

</div>

<div class="card-body">

<form method="POST">

<div class="row">

<div class="col-md-6 mb-3">
<label>First Name</label>
<input
type="text"
id="first_name"
name="first_name"
class="form-control"
value="<?php echo $customerData['first_name']??'';?>"
readonly>
</div>

<div class="col-md-6 mb-3">
<label>Last Name</label>
<input
type="text"
id="last_name"
name="last_name"
class="form-control"
value="<?php echo $customerData['last_name']??'';?>"
readonly>
</div>

<div class="col-md-6 mb-3">

<label>Gender</label>

<select
id="gender"
name="gender"
class="form-control"
disabled>

<option value="Male" <?php if(($customerData['gender']??'')=="Male") echo "selected"; ?>>Male</option>

<option value="Female" <?php if(($customerData['gender']??'')=="Female") echo "selected"; ?>>Female</option>

<option value="Other" <?php if(($customerData['gender']??'')=="Other") echo "selected"; ?>>Other</option>

</select>

</div>
<div class="col-md-6 mb-3">

<label>Phone</label>

<input
type="text"
id="phone"
name="phone"
class="form-control"
value="<?php echo $customerData['phone']??'';?>"
readonly>

</div>

<div class="col-md-6 mb-3">

<label>Email</label>

<input
type="email"
class="form-control"
value="<?php echo $email;?>"
readonly>

</div>

<div class="col-md-6 mb-3">

<label>ID Proof</label>

<input
type="text"
id="id_proof"
name="id_proof"
class="form-control"
value="<?php echo $customerData['id_proof']??'';?>"
readonly>

</div>

<div class="col-md-12 mb-3">

<label>Address</label>

<textarea
id="address"
name="address"
class="form-control"
rows="3"
readonly><?php echo $customerData['address']??'';?></textarea>

</div>

</div>

<button
type="button"
id="editBtn"
class="btn btn-primary">

Edit Profile

</button>

<button
type="submit"
name="update"
id="saveBtn"
class="btn btn-success"
style="display:none;">

Save Changes

</button>

<a href="dashboard.php" class="btn btn-secondary">

Back

</a>

</form>

</div>

</div>

</div>

<script>

document.getElementById("editBtn").onclick=function(){

document.getElementById("first_name").readOnly=false;
document.getElementById("last_name").readOnly=false;
document.getElementById("phone").readOnly=false;
document.getElementById("id_proof").readOnly=false;
document.getElementById("address").readOnly=false;

document.getElementById("gender").disabled=false;

document.getElementById("editBtn").style.display="none";
document.getElementById("saveBtn").style.display="inline-block";

};

</script>

</body>
</html>