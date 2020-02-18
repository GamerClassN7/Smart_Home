#! /bin/sh
apt install dialog;

#dialog --stdout --title "Smart-Home" \
  #--backtitle "Select update chanel :)" \
  #--yesno "DEV: Experimental Version, STABLE:  Stable version" 7 60
#dialog_status=$?

# Do something
dialog --menu "The best tortilla is:" 0 0 0 1 "with onions" 2 "without onion" 3 "with piminetos"
echo "answer $?";
#if [ "$dialo_status" -eq 'DEV' ]; then
  # The previous dialog was answered Yes
  #git checkout dev;
#else
  # The previous dialog was answered No or interrupted with <C-c>
  #git checkout master;
#fi

#clear;

#git reset --hard HEAD
#git pull

#rm ./.gitignore
#rm ./.ftpignore
#rm ./.todo
#rm ./LICENCE
#rm ./README.md
#rm -rf ./_FIRMWARE/*
#rm -rf ./_INSTALATION/*
#rm -rf ./_README_IMG/*

echo "Done";
