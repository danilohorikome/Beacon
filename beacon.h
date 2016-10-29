#include <stdio.h>
#include <string.h>
#include <errno.h>
#include <wiringSerial.h>
#include <stdlib.h>
#include <mysql/mysql.h>
#include <time.h>
#include <math.h>

#define A 0.89976
#define B 7.7095
#define C 0.111

int iniciaSerial(char* msg);
int contaBeacon(char* msg);
double calculaDist(int rssi, int txpower);
double distanciaRecalc(double* vet);
void baseDados();
void formataString(char* msg, int numBeacons);