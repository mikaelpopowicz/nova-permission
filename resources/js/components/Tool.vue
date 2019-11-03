<template>
    <div>
        <heading class="mb-6">{{ __('permission-builder.title') }}</heading>

        <card>
            <loading-view :loading="loading">
                <table
                    v-if="roles.length > 0"
                    class="table w-full"
                    cellpadding="0"
                    cellspacing="0"
                    data-testid="resource-table"
                >
                    <thead>
                        <tr>
                            <th class="w-1/6 bg-white"></th>
                            <th v-for="role in roles" class="bg-white">
                                <checkbox
                                    class="py-2 justify-center"
                                />
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="(permissions, name) in groups">
                            <tr>
                                <th class="text-left text-primary w-1/6 border-b-0">{{ name }}</th>
                                <th v-for="role in roles" class="border-b-0">
                                    <p>{{ role.name}}</p>
                                    <checkbox
                                        class="py-2 justify-center mt-2"
                                        :checked="groupsRolesChecked[name][role.id]"
                                    />
                                </th>
                            </tr>
                            <tr v-for="permission in permissions">
                                <td class="w-1/6">{{ permission.name }} <small>{{ permission.guard}}</small></td>
                                <td v-for="role in roles" class="text-center">
                                    <checkbox
                                        class="py-2 justify-center"
                                        :checked="permission.roles[role.id]"
                                    />
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </loading-view>
        </card>
    </div>
</template>

<script>
import api from '../api'
export default {
    data: () => ({
        config: Nova.config.PermissionBuilder || {},
        loading: false,
        roles: [],
        groups: [],
        loaders: {},
    }),
    mounted() {

        this.loading = true
        this.fetchData()
            .finally(() => this.loading = false)
    },
    computed: {
        rolesChecked() {
            let checked = {}

            console.log(this.groups.values())
            _.each(this.roles, role => {

            })

            return checked
        },
        groupsRolesChecked() {
            let checked = {}

            _.each(this.groups, (permissions, name) => {
                checked[name] = {}
                _.each(this.roles, role => {
                    checked[name][role.id] = permissions.map(p => {
                        return p.roles[role.id]
                    }).every(e => e === true)
                })
            })

            return checked
        }
    },
    methods: {
        fetchData() {
            return api.getPermissions()
              .then(response => {
                  this.roles = response.data.roles || []
                  let loaders = {}
                  let groups = response.data.groups || []
                  _.each(groups, permissions => {
                      _.each(permissions, permission => {
                          _.each(this.roles, role => {
                              if (! loaders.hasOwnProperty(permission.id)) {
                                  loaders[permission.id] = {}
                              }
                              loaders[permission.id][role.id] = false
                          })
                      })
                  })
                  this.groups = groups
                  this.loaders = loaders
              })
        },
        snake(string) {
            return string.replace(' ', '_').toLowerCase()
        },
    }
}
</script>

<style>
/* Scoped Styles */
</style>
