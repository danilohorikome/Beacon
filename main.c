#include "beacon.h"

int main()
{
   while(1){
	char msg[1000];
  	int  numBeacon;

	iniciaSerial(msg);
	numBeacon = contaBeacon(msg);
	formataString(msg, numBeacon);
    }
}