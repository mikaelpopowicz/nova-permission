const baseUrl = '/nova-vendor/nova-permission'

export default {
    getPermissions() {
        return Nova.request().get(`${baseUrl}/permissions`)
    }
}
