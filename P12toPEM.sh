#/bin/bash

# windows users can use this script: 
# 1) download openssl from http://indy.fulgan.com/SSL/
# 2) download bash from http://win-bash.sourceforge.net/

# check basename
b=$(basename $1 .p12)
if [ ! -f ${b}.p12 ]; then exit; fi

# ask password
read -s -p "Password: " secret

# extraxt the key from p12, encipher it whith des3, store it into pem
openssl pkcs12 -in ${b}.p12 -nocerts -passin "pass:$secret" -nodes | openssl rsa -des3 -out ${b}.pem -passout "pass:$secret"

# extraxt certificate from p12 and append to the key
openssl pkcs12 -in ${b}.p12 -clcerts -nokeys -passin "pass:$secret" >> ${b}.pem

exit

