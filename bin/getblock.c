/*
This program parses output from XdagJ getBlockByNumber or getBlockByHash RPC and outputs simplified JSON block payload (without transactions list) and a separate transactions list as CSV.
It expects to *ONLY* consume specified XdagJ RPC output, any other input will produce undefined results.

Block not found example input: {"jsonrpc":"2.0","id":2175,"result":null}
Block found example input: {"jsonrpc":"2.0","id":3334,"result":{"height":65,"balance":"1624.000000000","blockTime":1672092863999,"timeStamp":1712223092735,"state":"Main","hash":"b7c01d85dc6e629bb4974dd796da2ff5bbd5d4e664145916ad0680765b8dd4da","address":"2tSNW3aABq0WWRRk5tTVu/Uv2pbXTZe0","remark":"node1","diff":"0xbda9c97ad8","type":"Main","flags":"3f","refs":[{"direction":2,"address":"2tSNW3aABq0WWRRk5tTVu/Uv2pbXTZe0","hashlow":"0000000000000000b4974dd796da2ff5bbd5d4e664145916ad0680765b8dd4da","amount":"0.000000000"},{"direction":1,"address":"1Keb6dSjYC9Bx25RXPxJn70Uw2ee8Ml4","hashlow":"000000000000000078c9f09e67c314bd9f49fc5c516ec7412f60a3d4e99ba7d4","amount":"0.000000000"}],"transactions":[{"direction":2,"hashlow":"0000000000000000b4974dd796da2ff5bbd5d4e664145916ad0680765b8dd4da","address":"2tSNW3aABq0WWRRk5tTVu/Uv2pbXTZe0","amount":"1024.000000000","time":1672092863999,"remark":"node1"},{"direction":0,"hashlow":"000000000000000061c30e987a2c2dcfd26d532aaf8539cbbdf9b843a5c4449f","address":"n0TEpUO4+b3LOYWvKlNt0s8tLHqYDsNh","amount":"500.000000000","time":1672175726020,"remark":"!\"#$%&,()*+,-./:;<=>?@[\\]^_`{|}~"},{"direction":0,"hashlow":"00000000000000008b4244572ac98a7d21aa9592f2ef0c5cef9a64e3d4b43bfd","address":"/Tu01ONkmu9cDO/ykpWqIX2KySpXREKL","amount":"100.000000000","time":1672176851830,"remark":""}]}}
*/

#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <string.h>
#include <time.h>
#include <errno.h>

#ifdef _WIN32
	#include <winsock2.h>
	#include <ws2tcpip.h>
	#include <windows.h>
	#pragma comment(lib, "ws2_32.lib")
#else
	#include <sys/socket.h>
	#include <netdb.h>
	#include <netinet/in.h>
#endif

#define pbail(msg, code) puts(msg); returnCode = code; goto bail;

void die(const char *message, int returnCode)
{
	perror("Last error");
	fprintf(stderr, "%s\n", message);
	exit(returnCode);
}

int main(int argc,char *argv[])
{
	if (argc != 6)
		die("Arguments: Host/IP portNumber blockId outputPathJson outputPathCsv", 1);

	char *hostName, *blockId, *outputPathJson, *outputPathCsv;
	int portNumber;
	hostName = argv[1];
	portNumber = atoi(argv[2]);
	blockId = argv[3];
	outputPathJson = argv[4];
	outputPathCsv = argv[5];

	if (portNumber < 1 || portNumber > 65535)
		die("Invalid port number.", 2);

	if (strlen(blockId) > 64)
		die("Invalid block id.", 2);

	srand(time(NULL));

#ifdef _WIN32
	WSADATA wsa;

	if (WSAStartup(MAKEWORD(2, 2), &wsa) < 0)
		die("Unable to initialise windows sockets.", 3);

	SOCKET sock;
#else
	int sock;
#endif

	sock = socket(AF_INET, SOCK_STREAM, 0);

#ifdef _WIN32
	if (sock == INVALID_SOCKET) {
		fprintf(stderr, "WS last error: %d\n", WSAGetLastError());
		die("Could not create socket.", 4);
	}

	DWORD readTimeout = 60 * 1000;
#else
	if (sock < 0)
		die("Could not create socket.", 4);

	struct timeval readTimeout;
	readTimeout.tv_sec = 60;
	readTimeout.tv_usec = 0;
#endif

	if (setsockopt(sock, SOL_SOCKET, SO_RCVTIMEO, (const char *) &readTimeout, sizeof(readTimeout)) < 0)
		die("Unable to set socket read timeout.", 5);

	struct hostent *host;
	struct sockaddr_in hostAddr;
	memset(&hostAddr, 0, sizeof(hostAddr));

	host = gethostbyname(hostName);

	if (!host)
		die("Could not resolve host name.", 6);

	memcpy(&hostAddr.sin_addr.s_addr, host->h_addr, host->h_length);
	hostAddr.sin_family = AF_INET;
	hostAddr.sin_port = htons(portNumber);

	char payload[512];
	char request[1024];
	char response[1024];
	ssize_t received;

	memset(response, 0, sizeof(response));

	// XDAG-ADDRESS related code
	if (sprintf(payload, "{\"jsonrpc\":\"2.0\",\"method\":\"%s\",\"params\":[\"%s\",\"1\",\"1000000000\"],\"id\": %d}", strlen(blockId) < 26 ? "xdag_getBlockByNumber" : "xdag_getBlockByHash", blockId, rand()) < 0)
		die("Preparing payload failed.", 7);

	if (sprintf(request, "POST / HTTP/1.1\r\nHost: %s\r\nContent-Type: application/json\r\nConnection: close\r\nAccept: application/json\r\nContent-Length: %lu\r\n\r\n%s", hostName, strlen(payload), payload) < 0)
		die("Preparing request failed.", 8);

	if (connect(sock, (struct sockaddr *) &hostAddr, sizeof(hostAddr)) < 0)
		die("Connection to host failed.", 9);

	if (send(sock, request, strlen(request), 0) < 0)
		die("Sending request failed.", 10);

	FILE *outputFileJson;
	outputFileJson = fopen(outputPathJson, "w+b");

	if (!outputFileJson)
		die("Unable to open output JSON file.", 11);

	FILE *outputFileCsv;
	outputFileCsv = fopen(outputPathCsv, "w+b");

	if (!outputFileCsv)
		die("Unable to open output CSV file.", 11);

	int returnCode = 0;
	int i, i1 = 0, state = 0, csvState = 0, escapeSet = 0;
	char c, stopC, o;

	char *httpVerb = "HTTP/1.1 ";
	char *endOfHeaders = "\r\n\r\n";
	char *jsonStopSeq = ",\"transactions\":[";

	while ((received = recv(sock, response, sizeof(response) - 1, 0)) > 0) {
		//fwrite(response, received, 1, stdout);

		i = 0;

		while (i < received) {
			c = response[i];

			switch (state) {
				case 0:
					// reading HTTP response verb
					if (c != *(httpVerb + i1)) {
						pbail("Unexpected HTTP response verb.", 50);
					}

					if (i1 == 8) {
						i1 = 0;
						state++;
					} else {
						i1++;
					}
				break;
				case 1:
					// reading until \r\n\r\n
					if (c != *(endOfHeaders + i1)) {
						i1 = 0;
						break;
					}

					if (i1 == 3) {
						i1 = 0;
						state++;
					} else {
						i1++;
					}
				break;
				case 2:
					// dump JSON until jsonStopSeq
					if (fputc(c, outputFileJson) != c) {
						pbail("Unable to write JSON character.", 60);
					}

					if (c != *(jsonStopSeq + i1)) {
						i1 = 0;
						break;
					}

					if (i1 == 16) {
						// close JSON output
						if (fputc(']', outputFileJson) != ']') {
							pbail("Unable to write JSON character.", 60);
						}

						if (fputc('}', outputFileJson) != '}') {
							pbail("Unable to write JSON character.", 60);
						}

						if (fputc('}', outputFileJson) != '}') {
							pbail("Unable to write JSON character.", 60);
						}

						i1 = 0;
						state++;
					} else {
						i1++;
					}
				break;
				case 3:
					// dump JSON as CSV
					o = 0;

					switch (csvState) {
						case 0:
							// make sure we don't hit "]" before first quote (end of data)
							if (c == ']')
								state++;
						case 1:
							// read until quote
							if (c == '"')
								csvState++;
						break;
						case 2:
							// read until colon
							if (c == ':') {
								csvState++;
								escapeSet = 0;

								if (i1 == -1) {
									// insert newline
									if (fputc(10, outputFileCsv) != 10) {
										pbail("Unable to write CSV character.", 70);
									}

									i1 = 0;
								} else if (stopC == '"' && fputc(',', outputFileCsv) != ',') { // otherwise insert comma if previous stopC is '"'
									pbail("Unable to write CSV character.", 70);
								}

								stopC = 0;
							}
						break;
						case 3:
							// dump value until next key is encountered
							// remark will only contain StringUtils.isAsciiPrintable() characters - https://www.educative.io/answers/what-is-stringutilsisasciiprintable-in-java
							// XdagJ currently only escapes double quotes and backslashes in remak value
							if (!stopC) {
								stopC = (c == '"' ? '"' : ',');
								o = c;
								break;
							}

							if (!escapeSet && c == '\\') {
								escapeSet = 1;
								// don't output escape backslash
								break;
							}

							if (escapeSet) {
								escapeSet = 0;

								if (c == '"' && fputc(c, outputFileCsv) != c) { // insert extra double quote to properly escape double quote in context of CSV
									pbail("Unable to write CSV character.", 70);
								}

								o = c; // output escaped character
								break;
							}

							if (c == stopC) {
								if (i1 != 1)
									o = c;

								csvState = 0;
								i1 = i1 == 5 ? -1 : i1 + 1;
								break;
							}

							o = c;
						break;
						default:
							pbail("Invalid CSV parser state.", csvState);
					}

					if (o && i1 != 1 && fputc(o, outputFileCsv) != o) {
						pbail("Unable to write CSV character.", 70);
					}
				break;
				case 4:
					// ignore rest of file (end of data)
				break;
				default:
					pbail("Invalid internal state.", state);
			}

			i++;
		}
	}

	if (received < 0)
		die("Reading response failed.", 13);

bail:
	fclose(outputFileJson);
	fclose(outputFileCsv);

#ifdef _WIN32
	closesocket(sock);
	WSACleanup();
#else
	close(sock);
#endif

	return returnCode;
}
