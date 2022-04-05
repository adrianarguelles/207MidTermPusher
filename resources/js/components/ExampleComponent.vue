<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Example Component</div>

                    <div class="card-body">
                        <div>{{content}}</div>

                        <p>The button below has been clicked {{clicks}} times!</p>
                        <button class="btn btn-primary" @click="clicks++">+1</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                clicks: 0,
                content: "I'm an example component made in Vue."
            }
        },
        mounted() {
            console.log('Component mounted.')
        }
    }
</script>
