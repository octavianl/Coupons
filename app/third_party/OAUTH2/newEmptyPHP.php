


1. GET FIRST TIME TOKEN

    https://api.rakutenmarketing.com/token
    Authorization: Basic RDNSM0pUZnJldjFuWXlEZ1dpbFRzZjNUZk9JYTpiNTJlUHlvbVh2YkM3QVlPamhRVFQzRUdockVh
    grant_type=password&username=thelichking&password=arthas123&scope=2531438
            //--- 2531438 - coupon-land.com ---//
            //--- 2901923 - save-coupon.com ---//
    
    SE OBTINE
    
    {
        token_type: "bearer"
        expires_in: 3600
        refresh_token: "2ad523e2f625f68c8774df4dc30317b"
        access_token: "b0d63c78498b1ea9e7bb93c915718fe"
    }
    
2. EXTEND TOKEN

    https://api.rakutenmarketing.com/token
    
    "Content-type: application/x-www-form-urlencoded",
    "Accept: */*",
    "Cache-Control: no-cache",
    "Pragma: no-cache",
    "Authorization: Basic RDNSM0pUZnJldjFuWXlEZ1dpbFRzZjNUZk9JYTpiNTJlUHlvbVh2YkM3QVlPamhRVFQzRUdockVh
    
    grant_type=refresh_token&refresh_token=$refreshToken&scope=PRODUCTION
    
    SE OBTINE
    
    {
        token_type: "bearer"
        expires_in: 3600
        refresh_token: "9ce3bdab341c477989fba7e1b3d53d3"
        access_token: "4f3ce84345f5a9869c62efce821ec17"
    }
    
    
    
    
