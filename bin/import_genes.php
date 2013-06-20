<?php 

$dsn = 'mysql:dbname=chrdb;host=127.0.0.1';
$user = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $user, $password);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$sql = "INSERT INTO gene (gene_id,symbol,description,chr) VALUES (:gid,:sym,:dsc,:chr) 
			ON DUPLICATE KEY UPDATE location = :loc, description = :dsc, 
			synonyms = :syn, full_name = :fnm, chr = :chr";
$stmt = $pdo->prepare($sql);

$i = 0;
if (($handle = fopen("Homo_sapiens.gene_info", "r")) !== FALSE) {
	while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
		//if ($i > 0) {
			//if ($i === 0) {
				//print_r($data);
			//}

			

			if (count($data) > 2) {
				if (!is_numeric($data['6'])) {
					//print_r($data);
				}
				//if ($data[2] === 'CFTR') {
					$stmt->execute(array(
						':gid' => $data[1],
						':sym' => $data[2],
						':loc' => $data[7],
						':chr' => $data[6],
						':dsc' => $data[8],
						':syn' => $data[4],
						':fnm' => $data[11]
					));
				//}
			}
		//}
		$i++;
	}
	fclose($handle);
}

//printf("Your total income in 2012 was: $%s\n", number_format($income));