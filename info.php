<!DOCTYPE html>
<html>
	<head>
		<style>
			body{
				padding-top: 10px;
				background-color: #eee;
			}

			h1{
				text-align: center;
			}

			table {
    			border-collapse: collapse;
    			width: 30%;
    			margin: 0 auto;
    			text-align: center;
    			position: relative;
    			top: 50px;
			}			

			th, td {
    			text-align: left;
    			padding: 8px;
    			text-align: center;
    			border: 1px solid #ddd;
			}

			tr:nth-child(even){background-color: #FFFAFA}

			th {
    			background-color: #2F4F4F;
    			color: white;
			}

			img{
				display: block;
    			margin: 0 auto;
			}
		</style>
	</head>

	<body>
		<h1>Sistema de Localização Hospitalar</h1>

		<?php
			$ip = gethostbyname('localhost');
		?>

		<img src='planta_hospital.png' alt="Planta Hospital" style="padding:10px;" />

		<?php //Script para pegar dados dos Beacons
			define( 'MYSQL_HOST', 'localhost' );
			define( 'MYSQL_USER', 'root' );
			define( 'MYSQL_PASSWORD', 'admin' );
			define( 'MYSQL_DB_NAME', 'BEACON' );

			//Conecta na base de dados 
			try
			{
			    $PDO = new PDO( 'mysql:host=' . MYSQL_HOST . ';dbname=' . MYSQL_DB_NAME, MYSQL_USER, MYSQL_PASSWORD );
			}

			//Caso error, retorna mensagem
			catch ( PDOException $e )
			{
			    echo 'Erro ao conectar com o MySQL: ' . $e->getMessage();
			}

			//Query para pegar os últimos dados agrupado DEVICEID e MACADR
			$sql = "SET sql_mode = ''";	
			$PDO->query($sql);
			$sql = "SELECT MAX(DATA_HORA), LOCALIZACAO, ENDERECO_MAC, DISTANCIA FROM INFO_BEACON GROUP BY LOCALIZACAO, ENDERECO_MAC";	
			$sth = $PDO->prepare($sql);
			$sth->execute();

			$result = $sth->fetchAll(PDO::FETCH_ASSOC);
			$array_lenght = count($result); 
		?>

		<table>
			<tr>
				<th>Beacon (Equipamento)</th>
    			<th>Localização</th> 
    		</tr>
    			
			<?php
				//Imprime a localização dos Beacons
				for($i = 0; $i < $array_lenght; $i++)
				{
					$flag_dist = 1;
					$j = 0;
					for ($j1; $j < $array_lenght; $j++) 
					{ 
						if($result[$i]['ENDERECO_MAC'] == $result[$j]['ENDERECO_MAC'])
						{
							if($result[$i]['DISTANCIA'] > $result[$j]['DISTANCIA'])
							{
								$flag_dist = 0;
							}
						}
					}

					if($flag_dist == 1)
					{
						echo "<tr>";
						echo "<td>" . $result[$i]['ENDERECO_MAC'] . "</td>" ;
						echo "<td>" . $result[$i]['LOCALIZACAO'] . "</td>";
						echo "</tr>";
					}
				}

				$PDO=null;
			?>

			<?php
				//Atualiza a página
				//@PARAM sec ->tempo em segundos em que a pagina é atualizada
				$page = $_SERVER['PHP_SELF'];
				$sec = "10";
				header("Refresh: $sec; url=$page");
			?>
		</table>
		
	</body>
</html>

