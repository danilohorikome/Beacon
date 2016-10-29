main: main.c 
	gcc -o main `mysql_config --cflags --libs` beacon.c  main.c -lm -lwiringPi
