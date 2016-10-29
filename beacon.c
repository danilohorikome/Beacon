#include "beacon.h"

int serial_aux;
double vetor_aux1[10];
double vetor_aux2[10];
double vetor_aux3[10];
int flag_vetor1;
int flag_vetor2;
int flag_vetor3;


/*
 *  @desc Inicia a comunicação serial e envia comando e recebe resposta do bluetooth
 *  @param msg: vetor de string para receber a resposta do bluetooth
 *  @return -1 para erro e 1 para sucesso
 */
int iniciaSerial(char* msg) 
{
  int aux = 0;
  
  //Inicia a comunicação serial com baud rate de 9600
  serial_aux = serialOpen("/dev/ttyAMA0", 9600);

  //Se ocorrer erro na inicialização
  if(serial_aux == -1)
  {
    fprintf(stderr, "Comunicação serial falhou: %s\n", strerror(errno));
    return -1;
  }

  fflush(stdout);

  //Envia comando para o bluetooth
  serialPuts(serial_aux,"AT+DISI?");
  serialFlush(serial_aux);
  delay(2000);
  
  //Se ocorrer erro ao receber dados da serial
  if(serialDataAvail(serial_aux) == -1)
  {
    fprintf (stderr, "Erro ao receber dados: %s\n", strerror(errno));
    return -1;
  }

  //Recebe a resposta do bluetooth
  else
  {
    while(serialDataAvail(serial_aux) > 0)
    {
      delay(10);
      msg[aux] = serialGetchar(serial_aux);
      aux++;
    }
    serialFlush(serial_aux);
    msg[aux] = 0;
    //printf("\n%s\n", msg);
    return 1;
  }
}


/*
 *  @desc Conta o número de beacons descoberto pela raspberry
 *  @param msg: string que contém a resposta do bluetooth
 *  @return número de beacons descobertos
 */
int contaBeacon(char* msg)
{
  int count = 0;

  while(msg = strstr(msg, "OK+DISC:"))
  {
    count++;
    msg++;
  }
  //printf("%d\n",count);
  return count;   
}


/*
 *  @desc Calcula a distância entre beacon e a raspberry
 *  @param rssi: valor do RSSI enviado pelo beacon, 
 *         txpower: valor referência de intensidade do sinal enviado pelo beacon
 *  @return distância calculada
 */
double calculaDist(int rssi, int txpower)
{
  //Se não for possível calcular a distância  
  if(rssi == 0) return -1; 
   
  double rz = rssi*1.0/txpower;
  double dist;
  //Parâmetros abaixo variam com o dispositivo
  double a = 0.89976, b = 7.7095, c = 0.111;
   
  if(rz < 1.0)
  {
    dist = pow(rz,10);
  }

  else
  {
    dist = a * pow(rz,b) + c;
  }

  return dist;
}

/*
*  @desc Descarta os 3 valores extremos superiores e inferiores e realiza média das distâncias
*  @param vet: vetor com 10 volres de distância, 
*  @return distância média
*/
double distanciaRecalc(double* vet) 
{
  double temp;
  int i, j;
  
  // Ordena de forma crescente o vetor
  for(i = 0; i < 9; i++) 
  {
    for(j = i + 1; j < 10; j++) 
    {
      if(vet[j] < vet[i]) 
      {
        temp = vet[i];
        vet[i] = vet[j];
        vet[j] = temp;
      }
    }
  }

  //Realiza a média dos 6 valores de distância menos polarizados
  for(i=3; i<6; i++)
  {
    temp = vet[i] + vet[i+1];
  }
  
  return temp / 6;
}

/*
*  @desc Faz conexão com base de dados e insere dados na tabela
*/
void baseDados()
{
  char insert[500];
  char local[] = "Sala 1";

  //Inicializa a conexão com MySQL Server
  MYSQL sql;
  mysql_init(&sql);

  if(mysql_real_connect(&sql,"192.168.1.111","root","admin","beacon",0,NULL,0)) 
  {
    printf("Conexão com MySQL Server estabelecida\n");
  }
  
  else printf("Erro ao conectar ao MySQL Server\n");

  //Realiza a inserção dos dados na tabela
  if(flag_vetor1 == 10)
    sprintf(insert,"INSERT INTO info_beacon (Localizacao,Endereco_MAC,Distancia) VALUES ('%s','%s',%f)","Sala1","78A5048C47AF",distanciaRecalc(vetor_aux1));
  /*if(flag_vetor2 == 10)
    sprintf(insert,"INSERT INTO info_beacon (Localizacao,Endereco_MAC,Distancia) VALUES ('%s','%s',%f)","Sala1","mac",distanciaRecalc(vetor_aux2));
  if(flag_vetor3 == 10)
    sprintf(insert,"INSERT INTO info_beacon (Localizacao,Endereco_MAC,Distancia) VALUES ('%s','%s',%f)","Sala1","mac",distanciaRecalc(vetor_aux3));
  */

  if(mysql_query(&sql,insert))
  {
    printf("\nErro ao inserir os dados na tabela: %s\n", mysql_error(&sql));
  }

  else printf("Dados inseridos com sucesso na Base de Dados\n\n");
  
  //Fecha a conexão com MySQL Server
  mysql_close(&sql);       
}


/*
*  @desc Extrai os dados correspondentes do beacon a partir da string, guarda a distância no vetor global 
*  @param vet: vetor com 10 volres de distância, 
*  @return distância média
*/
void formataString(char* msg, int numBeacons)
{

  char mac[numBeacons][15];
  char rssi[numBeacons][6];
  char txPower[numBeacons][4];
  int rssi_int[numBeacons];
  int txPower_int[numBeacons];
  double dist[numBeacons];

  char *p;
  int aux;

  //Guarda os valores e dados da string vinda da serial nos arrays
  for(aux = 0; aux < numBeacons; aux++)
  {
    p = msg;
    p = p + 66 + (aux * 78);
    strncpy(txPower[aux], p, 2);
    txPower[aux][2] = '\0';
    p = p + 3;
    strncpy(mac[aux], p, 12);
    mac[aux][12] = '\0';
    p = p + 13;
    strncpy(rssi[aux], p, 4);
    rssi[aux][4] = '\0';
  }


  //for(aux=0;aux<numBeacons;aux++)
  //printf("\nMAC: %s Meas.Power: %s RSSI: %s\n",mac[aux],txPower[aux],rssi[aux]);

  //Faz conversão de Char para Int/Float
  for(aux = 0; aux < numBeacons; aux++)
  {
    txPower_int[aux] = strtol(txPower[aux], &p, 16);
    rssi_int[aux] = atoi(rssi[aux]) * -1;
    dist[aux] = calculaDist(rssi_int[aux], txPower_int[aux] - 256);
  }

  for(aux = 0; aux < numBeacons; aux++)
    printf("Beacon: %s | RSSI: %d | TxPower: %d | Distancia: %lf\n", mac[aux], rssi_int[aux], txPower_int[aux], dist[aux]);

  //Insere nos vetores globais de cada beacon a distância respectiva
  for(aux = 0; aux < numBeacons; aux++)
  {  
    if(strcmp("78A5048C47AF",mac[aux]) == 0)
    {
      vetor_aux1[flag_vetor1] = dist[aux];
      flag_vetor1++;
    }
    
    else if(strcmp("mac2",mac[aux]) == 0)
    {
      vetor_aux2[flag_vetor2] = dist[aux];
      flag_vetor2++;
    }

    else if(strcmp("mac3",mac[aux]) == 0)
    {
      vetor_aux3[flag_vetor3] = dist[aux];
      flag_vetor3++;
    }
  }

  printf("Flag1 = %d, Flag2 = %d, Flag3 = %d\n\n", flag_vetor1, flag_vetor2, flag_vetor3);
  //Faz inserção no banco de dados
  if(flag_vetor1 == 10 || flag_vetor2 == 10 || flag_vetor3 == 10)
  {
    baseDados();
    flag_vetor1 = 0;
    flag_vetor2 = 0;
    flag_vetor3 = 0;
    memset(mac, 0, numBeacons);
  }
}




