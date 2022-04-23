<template>
    <div class="UploadButton-container">
        <button type="button" class="UploadButton" title="Upload Image" data-bs-toggle="modal" data-bs-target="#FileUploadModal">
            <ion-icon name="attach-outline"></ion-icon>
        </button>

        <!-- File Dialog Modal -->
        <div id="FileUploadModal" class="modal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="modal-title">Upload a File</div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <form class="container" ref="uploadForm">
                            <div class="form-group">
                                <label for="attachment">Attachment: </label>
                                <input class="form-control" type="file" v-bind:disabled="isUploading" accept="image/*" name="attachment" id="attachment" v-on:change="onFileChange($event)">
                            </div>

                            <div class="text-danger" v-for="error in errors" v-bind:key="error">
                                {{error}}
                            </div>

                            <div class="FileUploadModal-actions gap-2">
                                <button type="button" v-bind:disabled="isUploading" class="btn btn-primary UploadAndSendBtn" v-on:click="uploadFile()">
                                    <!-- Spinner -->
                                    <span class="spinner-border text-light spinner-border-sm" role="status" v-if="isUploading">
                                        <span class="visually-hidden">Uploading...</span>
                                    </span>
                                    <!-- Spinner -->

                                    <span>Upload &amp; Send</span>
                                </button>
                                <button type="button" class="btn btn-secondary" style="height: 50%;" data-bs-dismiss="modal" v-bind:disabled="isUploading">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
    .UploadButton {
        /* cursor:  pointer; */
        font-size:  2em;
        max-height: min-content;
        padding: 0;
        color:  whitesmoke;
        background: none;
        border: none;

        display: flex;
        justify-content: center;
        align-items: center;
    }

    .UploadAndSendBtn {
        background-color: #e06822;
        color: whitesmoke;
        border: none;
        padding: 5px;
        width: 50%;
    }

    .UploadAndSendBtn:hover{
        background-color:#1a9988;
        color: whitesmoke;
        border: none;
        padding: 5px;
        width: 50%;
    }

    .FileUploadModal-actions {
        display: flex;
        flex-flow: row nowrap;
        justify-content: space-evenly;
        margin-top: 0.75rem;
    }

    .FileUploadModal-actions > .btn {
       flex: 1; 
    }
</style>

<script>
export default {
    props: ['active-room'],

    emits: ['upload-success'],

    data() {
        return {
            errors: [],
            isUploading: false,
            attachmentFile: null,
        }
    },

    methods: {
        // Handle changes in file input
        onFileChange(event) {
            const files = event.target.files;
            if (files.length > 0) {
                // Just get the first file uploaded
                this.attachmentFile = files[0];
            }
            else {
                this.attachmentFile = null;
            }
        },

        validateInput() {
            let errors = [];

            if (!this.attachmentFile) {
                errors.push("Please upload a file.");
            }

            return errors;
        },
        
        // Send file to server
        uploadFile() {
            this.errors = this.validateInput();

            // Don't send if there are errors
            if (this.errors.length > 0) {
                return;
            }

            // Prepare to send the file to the server
            let formData = new FormData();
            formData.append('attachment', this.attachmentFile, this.attachmentFile.name);
            formData.append('room_id', this.$props.activeRoom);
            this.isUploading = true;

            // Send request to upload file to server
            axios.post('messages', formData)
                .then(response => {
                    const imageUrl = response.data.imageUrl;

                    // Fire an event to the parent component
                    this.$emit('upload-success', imageUrl);

                    // Reset the form
                    this.$refs.uploadForm.reset();

                    // Close the modal
                    var modalElement = document.getElementById("FileUploadModal");
                    var modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                    modal.hide();
                })
                .catch((err) => {
                    console.error(err);
                    this.errors.push('Unable to upload file. Please try again later.');
                })
                .finally(() => {
                    this.isUploading = false;
                });
        }
    }
}
</script>