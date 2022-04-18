<template>
    <div class="container">
        <h1>Sample Update Profile Form</h1>
        <form @submit.prevent="onSubmit()">
            <div>
                <label for="firstname">First Name</label>
                <input type="text" name="firstname" id="firstname" v-model="firstName">
            </div>

            <div>
                <label for="lastname">Last Name</label>
                <input type="text" name="lastname" id="lastname" v-model="lastName">
            </div>

            <div>
                <p>Current Profile Picture:</p>
                <img v-if="currentProfilePicture !== null" class="img-thumbnail" style="max-width: 300px;" :src="currentProfilePicture" alt="Profile picture">
                <p v-else-if="currentProfilePicture === null">No profile picture.</p>
            </div>

            <div>
                <label for="profilepicture">Profile Picture</label>
                <input type="file" name="profilepicture" id="profilepicture" @change="onFileChange($event)">
            </div>

            <!-- This part shows error messages if there are any -->
            <div class="text-danger">
                <div v-for="error in errors" :key="error">
                    {{error}}
                </div>
            </div>

            <div>
                <button class="btn btn-primary" type="submit">Save</button>
            </div>
        </form>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                firstName: '',
                lastName: '',
                currentProfilePicture: '',
                profilePicture: null,
                errors: []
            }
        },
        created() {
            // Retrieve the current user's details so we can show the current values on the form
            axios.get('/profile')
                .then(response => {                    
                    this.firstName = response.data.firstName;
                    this.lastName = response.data.lastName;
                    this.currentProfilePicture = response.data.profilePicture;
                });
        },
        methods: {
            // Ensures the profile form contains valid input
            validate() {
                this.errors = [];

                if (this.firstName.length === 0) {
                    this.errors.push('Please enter a first name.');
                }

                if (this.lastName.length === 0) {
                    this.errors.push('Please enter a last name.');
                }
            },

            // Sets the data.profilePicture whenever the selected file changes
            // Necessary because there's no `v-model` for file inputs
            onFileChange(event) {
                const files = event.target.files;
                if (files.length > 0) {
                    // Just get the first file uploaded
                    this.profilePicture = files[0];
                }
                else {
                    this.profilePicture = null;
                }
            },

            onSubmit() {
                // Validate input first
                this.validate();

                if (this.errors.length > 0) {
                    console.log(this.errors);
                    return;
                }

                // Build a FormData object which we will use to send the form data to the server
                let formData = new FormData();
                formData.append('firstName', this.firstName);
                formData.append('lastName', this.lastName);

                // Append to form data only if the user uploaded a profile picture file
                if (this.profilePicture) {
                    formData.append('profilePicture', this.profilePicture);
                }

                axios.post('/profile', formData)
                    .then(response => {
                        // Code in this part is run when the profile was updated successfully.
                        
                        alert('Profile successfully updated!');

                        // You can get data from the server via the `response.data` object.

                        // e.g., response.data.profilePicture contains a URL to the newly updated profile picture
                    });
            }
        }
    }
</script>
