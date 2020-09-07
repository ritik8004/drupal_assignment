vcl 4.0;
#import std;

# Default backend definition. Points to Apache, normally.
backend default {
    .host = "appserver";
    .port = "80";
    .first_byte_timeout = 300s;
}
