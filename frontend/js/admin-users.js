// Admin Users Management Script
document.addEventListener('DOMContentLoaded', async () => {
    try {
        await loadUsers();
    } catch (error) {
        console.error('Error loading users:', error);
    }
});

async function loadUsers() {
    try {
        const response = await apiRequest('/admin/get-users.php');
        if (response && response.success) {
            const container = document.getElementById('usersContainer');
            
            if (response.data.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #999;">No users found</p>';
            } else {
                container.innerHTML = `
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Joined Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                            </tbody>
                        </table>
                    </div>
                `;
                
                const tbody = document.getElementById('usersTableBody');
                response.data.forEach(user => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${user.nama}</td>
                        <td>${user.email}</td>
                        <td>${user.no_telp || '-'}</td>
                        <td>${formatDate(user.created_at)}</td>
                        <td><a href="javascript:viewUser(${user.id_user})" class="btn btn-sm">View</a></td>
                    `;
                    tbody.appendChild(row);
                });
            }
        }
    } catch (error) {
        console.error('Error loading users:', error);
    }
}

function viewUser(userId) {
    alert('View user details page would be implemented here');
}
