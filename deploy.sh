#!/bin/bash
git clone https://github.com/spanjaan/merokosis.git
cd merokosis
composer install
cp -r * /home/merokosi/public_html
