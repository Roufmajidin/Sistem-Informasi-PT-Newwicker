<!DOCTYPE html>
<html>
<head>
    <title>Import Excel</title>
</head>
<body>

<h3>Upload Excel</h3>

<form action="{{ route('pfi.import.preview') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file" required>
    <button type="submit">Preview</button>
</form>

</body>
</html>
