<div class="row">
    <div class="col-md-12">
        {if $error}
            <div class="alert alert-danger">
                <strong>Error:</strong> {$error}
            </div>
        {else}
            <h3>Server Information</h3>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">Server Details</h4>
                        </div>
                        <div class="panel-body">
                            <table class="table table-striped">
                                <tr>
                                    <td><strong>Server Name:</strong></td>
                                    <td>{$server.name}</td>
                                </tr>
                                <tr>
                                    <td><strong>Hostname:</strong></td>
                                    <td>{$hostname}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        {if $status eq 'running'}
                                            <span class="label label-success">Running</span>
                                        {elseif $status eq 'stopped'}
                                            <span class="label label-danger">Stopped</span>
                                        {elseif $status eq 'installing'}
                                            <span class="label label-warning">Installing</span>
                                        {else}
                                            <span class="label label-default">{$status|default:'Unknown'}</span>
                                        {/if}
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>VM ID:</strong></td>
                                    <td>{$vmid}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">Resource Allocation</h4>
                        </div>
                        <div class="panel-body">
                            <table class="table table-striped">
                                <tr>
                                    <td><strong>CPU Cores:</strong></td>
                                    <td>{$cpu}</td>
                                </tr>
                                <tr>
                                    <td><strong>Memory:</strong></td>
                                    <td>{$memory} MB</td>
                                </tr>
                                <tr>
                                    <td><strong>Disk Space:</strong></td>
                                    <td>{$disk} MB</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            {if $ipAddresses}
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">IP Addresses</h4>
                        </div>
                        <div class="panel-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Address</th>
                                        <th>CIDR</th>
                                        <th>Gateway</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach $ipAddresses as $ip}
                                    <tr>
                                        <td>{$ip.type|upper}</td>
                                        <td><code>{$ip.address}</code></td>
                                        <td>{$ip.cidr}</td>
                                        <td>{$ip.gateway}</td>
                                    </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            {/if}
            
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">Server Management</h4>
                        </div>
                        <div class="panel-body">
                            <div class="btn-group" role="group">
                                <a href="{$serverUrl}/server/{$server.uuid}" 
                                   target="_blank" 
                                   class="btn btn-primary">
                                    <i class="fa fa-external-link"></i> Access ConvoyPanel
                                </a>
                                
                                <button type="button" 
                                        class="btn btn-success" 
                                        onclick="performServerAction('start')">
                                    <i class="fa fa-play"></i> Start
                                </button>
                                
                                <button type="button" 
                                        class="btn btn-warning" 
                                        onclick="performServerAction('restart')">
                                    <i class="fa fa-refresh"></i> Restart
                                </button>
                                
                                <button type="button" 
                                        class="btn btn-danger" 
                                        onclick="performServerAction('stop')">
                                    <i class="fa fa-stop"></i> Stop
                                </button>
                            </div>
                            
                            <div class="alert alert-info" style="margin-top: 15px;">
                                <i class="fa fa-info-circle"></i>
                                <strong>Note:</strong> Click "Access ConvoyPanel" to manage your server directly through the ConvoyPanel interface.
                                You can perform advanced operations like creating snapshots, managing backups, and accessing the console.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {/if}
    </div>
</div>

<script>
function performServerAction(action) {
    if (confirm('Are you sure you want to ' + action + ' the server?')) {
        // You can implement AJAX calls here to perform server actions
        // For now, we'll just redirect to the ConvoyPanel
        window.open('{$serverUrl}/server/{$server.uuid}', '_blank');
    }
}
</script>

<style>
.panel {
    margin-bottom: 20px;
}

.table code {
    background-color: #f5f5f5;
    padding: 2px 4px;
    border-radius: 3px;
}

.btn-group .btn {
    margin-right: 5px;
}

.label {
    font-size: 90%;
    padding: 4px 8px;
}
</style>