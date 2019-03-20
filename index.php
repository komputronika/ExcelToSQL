<!DOCTYPE doctype html>
<html lang="en">
<head>
    <title>Excel to MySQL Converter</title>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport">
    <link crossorigin="anonymous" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" rel="stylesheet">

    <style>
    body {
        margin: 1rem;
    }    
    .submit {
        margin-top: 1rem;
    }
    .container {
        padding-right:0;
        padding-left:0;
    }

    h3 {
        margin-bottom: 0.0 !important;
    }

    #convert-form {
        font-size: 120%;
    }
    </style>    

</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title font-weight-bold">MYSQL SQL Converter</h2>
                        <h3 class="card-subtitle mb-2 text-muted">From Excel (.xls/.xlsx) file</h4>
                      </div>
                    <div class="card-body">
                        
                        <p class="card-text">

<form id="convert-form" method="POST" action="convert.php">
  <div class="form-group">
    <label for="exampleFormControlFile1"><b>Choose Excel file</b></label>
    <input type="file" accept=".xls,.xlsx" class="form-control-file" id="exampleFormControlFile1">
  </div>


  <b>Time column</b>
  <div class="form-check">
      <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
      <label class="form-check-label" for="defaultCheck1">
        Automatically add: created_ad, updated_at, deleted_at, is_deleted
      </label>
  </div>
<br/>

<b>Type for ID Column</b>
<div class="form-check">
  <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios1" value="option1" checked>
  <label class="form-check-label" for="exampleRadios1">
    Unsigned Integer Auto Increment
  </label>
</div>
<div class="form-check">
  <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios2" value="option2">
  <label class="form-check-label" for="exampleRadios2">
    UUID() 
  </label>
</div>

<div class="submit">
  <button type="submit" class="btn btn-primary">Convert</button>
</div>    

</form>



                        </p>
                        
                  </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script crossorigin="anonymous" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" src="https://code.jquery.com/jquery-3.3.1.slim.min.js">
    </script>
    <script crossorigin="anonymous" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js">
    </script>
    <script crossorigin="anonymous" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js">
    </script>
</body>
</html>