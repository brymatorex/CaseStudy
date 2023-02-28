<?php



declare(strict_types=1);

include 'conn.php';

const USER_URL = 'https://randomuser.me/api/?inc=gender,name,email,location&results=5&seed=a9b25cd955e2035f';

$getcurrentworkingDirectory = getcwd();

$csv_provider = array_map('str_getcsv', file($getcurrentworkingDirectory . '/data/users.csv'));
// var_dump($csv_provider);
$csvProviders = []; // possible fix
array_walk($csvProviders, function (&$a) use ($csv_provider) {
    $a = array_combine($csv_provider[0], $a);
});
array_shift($csv_provider); # Remove header column

$url = USER_URL;
$web_provider = json_decode(file_get_contents($url))->results;
$pr = []; // possible fix 
array_walk($pr, function (&$a) use ($web_provider) {
    $a = array_combine($web_provider[0], $a);
}); // possible fix

$b = []; // possible fix
$i = 100000000000;
foreach ($web_provider as $item) {
    $i++;
    if ($item instanceof stdClass) {
        $b[] = [
            $i, // id
            $item->gender,
            $item->name->first . ' ' . $item->name->last,
            $item->location->country,
            $item->location->postcode,
            $item->email,
            (new Datetime('now'))->format('Y') // birhtday
        ];
    }
}
$providers = array_merge($csv_provider, $b); # merge arrays
// $database = new SQLite3($getcurrentworkingDirectory . '/database/users.dump');
// $database->exec('CREATE TABLE IF NOT EXISTS users (
//     id   INTEGER PRIMARY KEY,
//     email TEXT    NOT NULL,
//     name TEXT NOT NULL,
//     country TEXT)
// ');

// Create database

$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(50) NOT NULL,
    name VARCHAR(150) NOT NULL,
    country VARCHAR(50),
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

$conn->query($sql);

foreach ($providers as $user) {
    $result = $conn->query('SELECT count(*) FROM users WHERE id = ' . $user[0]);
    $num = $result->fetch_array();
      
    // var_dump($num);
    if ($num[0] === 0) {
        $email = $user[5];
        $name = $user[2];
        $id = $user[0];
        $country = $user[3];
        $conn->query("INSERT INTO users VALUES($id,'$email','$name','$country')");

    }

}

$conn->close();

echo 'Users imported!' . PHP_EOL;
