<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <style>
        body, html {
            height: 100%;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .container {
            height: 100%;
            justify-content: center;
        }

        #login-div{
            display:block; 
            width: 50%; 
            box-shadow: 20px 20px 50px 5px #4b6173;
            border-radius: 5px;
            padding:3%   
        }
        .main-content {
            position: relative;
            display: flex;
            margin-top: 5%;
            background-color: rgb(142, 202, 244);
            height: 70%;
            width: 100%;
            border-radius: 15px;
            box-shadow: 20px 20px 50px 5px #4b6173;
        }

        #loading{
            position: absolute;
            top: 50%;
            left: 35%;
            z-index: 1000;
            display: none;
        }

        .parent-modals {
            background-color: aliceblue;
            display: none;
            top: 5%;
            left: 2%;
            position: absolute;
            width: 70%;
            height: 90%;
        }

        #upload-zip-form {
            display: flex;
            height: 80%;
            width: 100%;
            justify-content: center;
            align-items: center;
        }

        #upload-app-form {
            display: grid;
            height: 80%;
            width: 100%;
            justify-content: center;
            align-items: center;
        }

        #modal-title {
            height: 10%;
            width: 100%;
            display: flex;
            justify-content: center;
        }

        #end-modal {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        #progress {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: rgb(142, 202, 244);
            width:26%;
            height:90%;
            top:5%;
            right:2%;
            position: absolute;
            background-color: ;
        }

    </style>
</head>
<body>
    <div id="login-div" style='display:block'>
        <form id="login-form">
            <h3>Enter Store Owner Email and Password</h3>
            <div class="form-group">
                <label for="exampleInputEmail1">Email address</label>
                <input type="email" name='email' class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter email" required>
            </div>
            <div class="form-group">
                <label for="exampleInputPassword1">Password</label>
                <input type="password" name='password' class="form-control" id="exampleInputPassword1" placeholder="Password" required>
            </div><br>
            <button type="submit" class="btn btn-primary form-control" id='login-button'>Login</button>
        </form>
    </div>
    <div class="container" style="display:none">
        <!-- <div id="logo" style="margin-left: 45%"><img src="images/logo.png" alt=""></div> -->

        <div class="main-content">
            <!-- Spinner -->
            <div class="spinner-border text-primary" role="status" id='loading'></div>
            <!-- Upload Zip Modal -->
            <div class="parent-modals" id="upload-zip-modal">
                <div id="modal-title">
                   <h3>Upload Imprintnext Package and License</h3>
                </div>
                <div id="upload-zip-form">
                    <form method="post" id="zip-form">
                        <label for="zip-file" class="form-control">Build File</label>
                        <input type="file" name="zip-file" id="zip-file" accept=".zip"  class="form-control" required><br><br>
                        <label for="txt-file" class="form-control">License Key</label>
                        <input type="file" name="txt-file" id="txt-file" accept=".txt"  class="form-control" required><br><br>
                        <button type="submit" class="btn btn-primary submit-button form-control">Upload</button>
                    </form>
                </div>
            </div>

            <!-- Fetch App Modal -->
            <div class="parent-modals" id="fetch-app-modal">
                <div id="modal-title">
                   <h1>Select Builds To Be Uploaded</h1>
                </div>
                <div id="upload-app-form">
                    <form method="post" id="app-form">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="admin" value="admin" disabled>
                            <label class="form-check-label" for="flexSwitchCheckChecked">Admin</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="designer" value="designer" disabled>
                            <label class="form-check-label" for="flexSwitchCheckChecked">Tool-Desktop</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="mobile" value="mobile" disabled>
                            <label class="form-check-label" for="flexSwitchCheckChecked">Tool-Mobile</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="api" value="api" disabled>
                            <label class="form-check-label" for="flexSwitchCheckChecked">API</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="quotation" value="quotation" disabled>
                            <label class="form-check-label" for="flexSwitchCheckChecked">Quotation</label>
                        </div><br><br>
                        <button type="submit" class="btn btn-primary  submit-button form-control" id="select-app-submit">Next</button>
                    </form>
                </div>
            </div>
            <div class="parent-modals" id="upload-complete">
                <div id="end-modal">
                    <h3>Build Uploaded Successfully</h3><br><br>
                    <a href='build_upload.php' class="btn btn-primary" id='terminate'>Upload Another Build</a>
                </div>
            </div>
            <div id="progress">
                <ul class="list-group text-center" style="width:90%">
                    <li class="list-group-item" id="step1">Upload Zip File</li><br>
                    <li class="list-group-item" id="step2">Fetch Builds</li><br>
                    <li class="list-group-item" id="step3">Upload License</li><br>
                    <li class="list-group-item" id="step4">Take Backup</li><br>
                    <li class="list-group-item" id="step5">Transfer Files</li><br>
                </ul>
            </div>
        </div>
    </div>
    <script>
        <?php
            $config_file_path = dirname(getcwd()) . DIRECTORY_SEPARATOR .'config.xml' ;
            $file_contents = file_get_contents($config_file_path);
            preg_match_all('/https:.*?designer\//', $file_contents, $matches);
            $base_url = '';
            foreach ($matches[0] as $match) {
                $base_url = $match;
            }
        ?>
        const base_url = '<?php echo $base_url;?>';
        var step = -1;
        var loc = ''; //temporary directory location
        function readJson() {
            $.ajax({
                url: base_url+'build-upload/upload-zip-file.php?action=readjson',
                type: 'GET',
                async: false,
                success: function (response) {    
                    response = JSON.parse(response);
                    respstep = response.step;
                    resploc = response.tempdir;
                    step = respstep;
                    loc = resploc;
                },
                error: function (xhr, status, error) {
                    console.error("Error:", error);
                    alert('Something Went Wrong');
                },
            });
        }

        function changeJson(step, tempdir) {
            $.ajax({
                url: base_url+"build-upload/upload-zip-file.php?action=changejson&step="+step+"&tempdir="+tempdir,
                type: 'GET',
                success: function (response) {   
                    
                },
                error: function (xhr, status, error) {
                    console.error("Error:", error);
                    alert('Something Went Wrong');
                },
            });
        }

        $('#login-form').submit(function(event){
            event.preventDefault();
            let formData = $(this).serialize();
            $.ajax({
                url: base_url+'build-upload/upload-zip-file.php?action=login',
                type: 'POST',
                data: formData,
                success: function (response) {    
                    let result = JSON.parse(response);
                    if (result.status == 0) {
                        alert(result.msg);
                        location.reload();
                    }else if (result.status = 1) {
                        readJson();
                        if (step == 0) {
                            $('#login-div').css('display', 'none');
                            $('.container').css('display', 'block');
                            uploadZip();
                        } else if(step == 1) {
                            $('#step1').css('background-color', '#78f599');
                            $('#login-div').css('display', 'none');
                            $('.container').css('display', 'block');
                            selectApps();
                        } else {
                            changeJson(0, )
                        }
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Error:", error);
                    alert('Something Went Wrong');
                },
            });
        });

        function uploadZip() {
            $('#upload-zip-modal').css('display','block');
            $('#upload-zip-modal').css('z-index','1');
            $('#zip-form').submit(function(event){
                event.preventDefault();
                addLoader();
                const formData = new FormData(this);
                $.ajax({
                    url: base_url+'build-upload/upload-zip-file.php?action=unzip',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        removeLoader();
                        let result = JSON.parse(response);
                        if (result.status == 1) {
                            loc = result.location;
                            step = 1;
                            changeJson(step, loc);
                            $('#step1').css('background-color', '#78f599');
                            $('#upload-zip-modal').css('display', 'none');
                            selectApps();
                        } else {
                            $('#step1').css('background-color', '#fc4e5a');
                            alert(result.msg);
                        }
                    },
                    error: function (xhr, status, error) {
                        $('#step1').css('background-color', '#fc4e5a');
                        console.error("Error:", error);
                        alert('Something went wrong');
                    },
                });
            });
        }

        function selectApps(){
            $('#fetch-app-modal').css('z-index', '1');
            $('#fetch-app-modal').css('display', 'block');
            let result = '';
            $.ajax({
                url: base_url+'build-upload/upload-zip-file.php?action=fetchapps&tempdir='+loc,
                type: 'GET',
                processData: false,
                contentType: false,
                success: function (response) {
                    result = JSON.parse(response);
                    $.each(result.apps, function(key, value){
                        $("#app-form input[name='"+value+"']").prop('checked', true);
                        $("#app-form input[name='"+value+"']").prop('disabled', false);
                    });
                },
                error: function () {
                    $('#step2').css('background-color', '#fc4e5a');
                    alert('Something went wrong');
                },
            });
        }
        $('#app-form').submit(function(event){
            event.preventDefault();
            if($('#app-form input[type="checkbox"]:checked').length == 0){
                alert("Select at least one Option");
                return;
            }
            const formData = new FormData(this);
            formData.append('dir',loc);
            $('#select-app-submit').prop('disabled', true);
            addLoader();
            $.ajax({
                url: base_url+'build-upload/upload-zip-file.php?action=apps',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    removeLoader();
                    step = 2;
                    $('#step2').css('background-color', '#78f599');
                    uploadLicense();
                },
                error: function () {
                    $('#step1').css('background-color', '#fc4e5a');
                    alert('Something went wrong');
                },
            });
        });

        function uploadLicense(){
            addLoader();
            $.ajax({
                url: base_url+'build-upload/upload-zip-file.php?action=license&dir='+loc,
                type: 'GET',
                processData: false,
                contentType: false,
                success: function (response) {
                    removeLoader();
                    step = 3;
                    $('#step3').css('background-color', '#78f599');
                    takeBackUp();
                },
                error: function (xhr, status, error) {
                    $('#step3').css('background-color', '#fc4e5a');
                    alert('Something went wrong');
                },
            });
        }

        function takeBackUp(){
            addLoader();
            $.ajax({
                url: base_url+'build-upload/upload-zip-file.php?action=backup&dir='+loc,
                type: 'GET',
                processData: false,
                contentType: false,
                success: function (response) {
                    removeLoader();
                    let resp = JSON.parse(response);
                    if (resp.status == 1) {
                        step = 4;
                        $('#step4').css('background-color', '#78f599');
                        transferFiles();
                    }
                    if (resp.status == 0) {
                        if (confirm(resp.msg)) {
                            alert($.each(resp.files, function(key, value){
                                value+',';
                            }));
                        }
                        step = 4;
                        if(!confirm("Backup Taken Manually ?")){
                            return;
                        }
                        $('#step4').css('background-color', '#78f599');
                    }
                },
                error: function () {
                    $('#step4').css('background-color', '#fc4e5a');
                    alert('Something went wrong');
                },
            });
        }

        function transferFiles(){
            addLoader();
            $.ajax({
                url: base_url+'build-upload/upload-zip-file.php?action=transfer&dir='+loc,
                type: 'GET',
                processData: false,
                contentType: false,
                success: function (response) {
                    removeLoader();
                    let resp = JSON.parse(response);
                    if (resp.status == 1) {
                        step = 5;
                        $('#step5').css('background-color', '#78f599');
                        $('#upload-complete').css('display', 'flex');
                        $('#upload-complete').css('align-items', 'center');
                        $('#upload-complete').css('justify-content', 'center');
                        $('#upload-complete').css('z-index', '1');
                        resetJsonData();
                    }else {
                        alert(resp.msg);
                        step = 5;
                        $('#step5').css('background-color', 'red');
                    }
                },
                error: function () {
                    $('#step5').css('background-color', '#fc4e5a');
                    alert('Something went wrong');
                },
            });
        }

        function addLoader(){
            $('#loading').css('display', 'block');
        }
        function removeLoader(){
            $('#loading').css('display', 'none');
        }

        function resetJsonData() {
            $.ajax({
                url: base_url+"build-upload/upload-zip-file.php?action=terminate&step=0&tempdir=",
                type: 'GET',
                success: function (response) {
                },
                error: function (xhr, status, error) {
                    console.error("Error:", error);
                    alert('Something Went Wrong');
                },
            });
        }
        
    </script>
</body>
</html>




