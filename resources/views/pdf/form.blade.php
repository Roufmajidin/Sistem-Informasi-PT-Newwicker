<form action="{{ route('pdf.convert') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <label for="pdf">Upload PDF:</label>
    <input type="file" name="pdf" accept="application/pdf" required>
    <button type="submit">Convert to Excel</button>
</form>
