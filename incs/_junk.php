   1. $sep = "sOmErAnDoMsTrInGfOrBoUnDaRy";
   2. $header = 'Content-Type: multipart/x-mixed-replace;boundary=' . $sep;
   3. header($header);
   4. header("pragma: no-store,no-cache");
   5. header("cache-control: no-cache,no-store,must-revalidate,max-age=-1");
   6. header("expires: -1");
   7. $boundary = "\n" . $sep . "\n";
   8. echo $boundary;
   9. set_time_limit(0);
  10. while (1) {
  11.     $handle = fopen("image.jpg", "rb");
  12.     if ($handle != false) {
  13.         echo "Content-Type: image/jpeg\n\n";
  14.         while (!feof($handle)) {
  15.             $imageContents = fread($handle, 1024);
  16.             if ($imageContents != false) = echo $imageContents;
  17.         }
  18.         fclose($handle);
  19.         echo $boundary;
  20.         usleep($waitTime);      
  21.     } else usleep($waitTime);
  22. }