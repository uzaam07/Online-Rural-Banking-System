<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bank";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Validate and sanitize input
$firstname = filter_input(INPUT_POST, 'fname', FILTER_SANITIZE_STRING);
if (!$firstname) {
    die("Invalid name provided");
}

// Use prepared statement
$stmt = $conn->prepare("SELECT fname, lname, loan, isdt, aadharno, email, ad, ph, ip, ir, pc, closingdate, closingbalance FROM MyGuest WHERE fname = ?");
$stmt->bind_param("s", $firstname);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // EMI calculation
        $myprincipal = $row["loan"];
        $myclosingb = $row["closingbalance"];
        $myinterest = ($row["ir"]/12.00/100.0);
        $myperiod = $row["ip"];
        
        $top = pow((1+$myinterest), $myperiod);
        $bottom = $top - 1;
        $sp = $top / $bottom;
        $emi = (($myprincipal * $myinterest) * $sp);
        $repaymentamt = $emi * $myperiod;
        $mynewinterest = $repaymentamt - $myprincipal;
        $mymonthinterest = $mynewinterest / $myperiod;
        
        $ddate = $row["isdt"];
        $datetoday = date("Y-m-d");
        $todaysdate = date('Y-m-d', strtotime($datetoday));
        
        // Output HTML
        echo '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>User Details</title>
    <link rel="stylesheet" href="css/style.css"> 
    <style>
        table, th, td {
            border: 5px solid black;
            border-collapse: collapse;
        }
        th, td, tr {
            padding: 5px;
            text-align: center;
        }
        table {
            width: 100%;    
            background-color: #4d3319;
        }
        tbody tr:hover {
            background: #862d59;
        }
    </style>
</head>
<body>
    <p><font size="25" color="#ffff80">Confirm your details</font></p>
    <p id="demo"></p>

    <script>
        document.getElementById("demo").innerHTML = Date();
    </script>
    <table style="width:60%">
        <tr>
            <th>Firstname</th>
            <th>'.htmlspecialchars($row["fname"]).'</th>  
        </tr>
        <tr>
            <td>Last Name</td>
            <td>'.htmlspecialchars($row["lname"]).'</td>
        </tr>
        <tr>
            <td>Aadhar Number</td>
            <td>'.htmlspecialchars($row["aadharno"]).'</td>
        </tr>
        <tr>
            <td>Loan Amount:</td>
            <td>Rs.'.htmlspecialchars($row["loan"]).'</td>  
        </tr>
        <tr>
            <td>Issue Date</td>
            <td>'.htmlspecialchars($row["isdt"]).'</td>  
        </tr>
        <tr>
            <td>Loan Period(in terms of months)</td>
            <td>'.htmlspecialchars($row["ip"]).'</td>  
        </tr>
        <tr>
            <td>Interest Rate(per anum)</td>
            <td>'.htmlspecialchars($row["ir"]).'%</td>  
        </tr>
        <tr>
            <td>Rate of interest per Month</td>
            <td>'.number_format($myinterest, 4).'</td>  
        </tr>
        <tr>
            <td>Email Id</td>
            <td>'.htmlspecialchars($row["email"]).'</td>  
        </tr>
        <tr>
            <td>Contact Number</td>
            <td>'.htmlspecialchars($row["ph"]).'</td>  
        </tr>
        <tr>
            <td>Address</td>
            <td>'.htmlspecialchars($row["ad"]).'</td>  
        </tr>
        <tr>
            <td>Pin Code</td>
            <td>'.htmlspecialchars($row["pc"]).'</td>  
        </tr>
    </table>

    <p><font size="25" color="#ffff80">Your Payment Details..</font></p>
    <table style="width:60%">
        <tr>
            <th>CLOSING BALANCE</th>
            <th>Rs.'.htmlspecialchars($row["closingbalance"]).'</th>  
        </tr>
        <tr>
            <td>Todays DATE</td>
            <td>'.htmlspecialchars($todaysdate).'</td>  
        </tr>
        <tr>
            <td>Loan EMI</td>
            <td>Rs.'.number_format($emi, 2).'</td>  
        </tr>
        <tr>
            <td>Tenure(in Months)</td>
            <td>'.htmlspecialchars($row["ip"]).'</td>  
        </tr>
        <tr>
            <td>Overall Interest</td>
            <td>Rs.'.number_format($mynewinterest, 2).'</td>  
        </tr>
        <tr>
            <td>payable Interest for this Month</td>
            <td>Rs.'.number_format($mymonthinterest, 2).'</td>  
        </tr>
        <tr>
            <td>Total Repayment Amount</td>
            <td>Rs.'.number_format($repaymentamt, 2).'</td>  
        </tr>
    </table>

    <form class="form-wrapper cf" name="lg" action="updatetry.php" method="post">
        <p><font color="#ff66b3" size="5">Enter Collected EMI Amount</font></p>
        <input type="number" name="amt" placeholder="Enter collected amount......" required>
        <input type="hidden" name="fname" value="'.htmlspecialchars($row["fname"]).'">
        <input type="hidden" name="closingdate" value="'.htmlspecialchars($todaysdate).'">
        <input type="hidden" name="closingbalance" value="'.htmlspecialchars($row["closingbalance"]).'">
        <input type="number" name="camt" placeholder="Confirm your amount...." required>
        <button type="submit">UPDATE</button>
    </form>
</body>
</html>';
    }
} else {
    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Not Found</title>	
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/stylegood.css">
    <p><font size="25" color="#ffff80">Sorry, the Name <font color="red">'.htmlspecialchars($firstname).'</font> not found in the database!</font></p>
</head>
<body>
    <a class="button" href="index.html">HOME</a>
</body>
</html>';
}

$stmt->close();
mysqli_close($conn);
?>
