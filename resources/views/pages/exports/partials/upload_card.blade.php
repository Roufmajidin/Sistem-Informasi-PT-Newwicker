<div class="doc-card">

    <div class="doc-header">

        <div>

            <h6>

                {{ $title }}

            </h6>

            <small>

                Upload dokumen

            </small>

        </div>

        <span class="status-badge empty">

            Belum Ada

        </span>

    </div>

    <label class="upload-box">

        <input

            type="file"

            name="{{ $multiple ? $name.'[]' : $name }}"

            {{ $multiple ? 'multiple' : '' }}

            class="file-input"

            hidden>

        <i class="fa fa-cloud-upload fa-3x mb-3"></i>

        <h6>

            Klik atau Drag File

        </h6>

        <small>

            PDF, DOCX, XLSX, PNG, JPG

        </small>

    </label>

    <div class="file-preview">

    </div>

</div>