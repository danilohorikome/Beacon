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
    			width: 60%;
    			margin: 0 auto;
    			text-align: center;
    			position: relative;
    			top: 50px;
			}			

			th, td {
    			text-align: center;
    			padding: 8px;
    			
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


		<img src='planta_hospital_escola.png' alt="Planta Hospital" style="padding:10px width="600" height="500" ;" />

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
			//$sql = "SET sql_mode = ''";	
			$sql = "SELECT LOCALIZACAO, ENDERECO_MAC, DISTANCIA, DATA_HORA FROM INFO_BEACON
WHERE DATA_HORA IN (SELECT MAX(DATA_HORA) FROM INFO_BEACON GROUP BY LOCALIZACAO, ENDERECO_MAC) GROUP BY LOCALIZACAO, ENDERECO_MAC";
			$PDO->query($sql);
				
			$sth = $PDO->prepare($sql);
			$sth->execute();

			$result = $sth->fetchAll(PDO::FETCH_ASSOC);
			
			$array_lenght = count($result); 
			//echo '<pre>'; print_r($result); echo '</pre>';
		?>

		<table>
			<tr>
				<th>Equipamento</th>
    			<th>Localização Atual</th>
    			<th>Distância</th>
    			<th>Erro</th>
    			<th>Última Localização</th>
    			<th>Data e Hora</th>

    		</tr>
    			
			<?php

				$sala_anterior1 = "Sala2";
				$sala_atual1 = "Sala3" ;
				$sala_anterior2 = "Sala4";
				$sala_atual2 = "Sala5";
				/*$sala_anterior1 = $result[0]['LOCALIZACAO'];
				$sala_atual1 = $result[0]['LOCALIZACAO'];
				$sala_anterior2 = $result[0]['LOCALIZACAO'];
				$sala_atual2 = $result[0]['LOCALIZACAO'];*/

				//Compara as distâncias para um mesmo mac
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

						if($result[$i]['ENDERECO_MAC'] == '0CF3EE031CB2')
						{
							echo "<td>" . "B1 - Desfibrilador" . "</td>";

						    if($result[$i]['LOCALIZACAO'] != $sala_atual1)
						    {
						    	$sala_anterior1 = $sala_atual1;
						    	$sala_atual1 = $result[$i]['LOCALIZACAO'];

						    }
						    echo "<td>" . $result[$i]['LOCALIZACAO'] . "</td>";
							echo "<td>" . $result[$i]['DISTANCIA'] . " m" . "</td>";
							//number_format($result[$i]['DISTANCIA'], 2)
							if($result[$i]['DISTANCIA'] <= 1)
								echo "<td>" . " +/- 0.3 m" . "</td>";
							else if($result[$i]['DISTANCIA'] <=4 && $result[$i]['DISTANCIA'] > 1)
								echo "<td>" . "+/- 0.7 m" . "</td>";
							else if($result[$i]['DISTANCIA'] <=6 && $result[$i]['DISTANCIA'] > 4)
								echo "<td>" . "+/- 2.5 m" . "</td>";
							else if($result[$i]['DISTANCIA'] <=10 && $result[$i]['DISTANCIA'] > 6)
								echo "<td>" . "+/- 4.0 m" . "</td>";
							else 
								echo "<td>" . "+/- 6.0 metros" . "</td>";

							echo "<td>" . $sala_anterior1 . "</td>";	
							echo "<td>" . $result[$i]['DATA_HORA'] . "</td>";
							echo "</tr>";

						}

						else if($result[$i]['ENDERECO_MAC'] == '78A5048C47AF')
						{
							echo "<td>" . "B2 - Eletrocardiógrafo" . "</td>";
						    if($result[$i]['LOCALIZACAO'] != $sala_atual2)
						    {
						    	$sala_anterior2 = $sala_atual2;
						    	$sala_atual2 = $result[$i]['LOCALIZACAO'];

						    }
							echo "<td>" . $result[$i]['LOCALIZACAO'] . "</td>";
							echo "<td>" . $result[$i]['DISTANCIA'] . " m" . "</td>";

							if($result[$i]['DISTANCIA'] <= 1)
								echo "<td>" . " +/- 0.3 m" . "</td>";
							else if($result[$i]['DISTANCIA'] <=4 && $result[$i]['DISTANCIA'] > 1)
								echo "<td>" . "+/- 0.7 m" . "</td>";
							else if($result[$i]['DISTANCIA'] <=6 && $result[$i]['DISTANCIA'] > 4)
								echo "<td>" . "+/- 2.5 m" . "</td>";
							else if($result[$i]['DISTANCIA'] <=10 && $result[$i]['DISTANCIA'] > 6)
								echo "<td>" . "+/- 4.0 m" . "</td>";
							else 
								echo "<td>" . "+/- 6.0 m" . "</td>";

							echo "<td>" . $sala_anterior2 . "</td>";
							echo "<td>" . $result[$i]['DATA_HORA'] . "</td>";
							echo "</tr>";
						}
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

