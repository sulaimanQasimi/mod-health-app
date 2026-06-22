import { useHttp } from '@inertiajs/react'
import { useEffect, useState } from 'react';

export default function GeneralReport() {

    const {data,setData,get,processing}=useHttp({query:''});
    const [appoinrmentReport,setAppoinrmentReport]=useState([]);
    useEffect(()=>{
        get('/react/api/reports/general/number-of-patients-base-on-department').then((response)=>{
            setAppoinrmentReport((response as any).data);
        });
    },[]);

    return (
        <div>
            <h1>General Report</h1>
            <table className="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Department Name</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    {appoinrmentReport?.map((item:any)=>(
                        <tr key={item.id}>
                            <td>{item.department_name}</td>
                            <td>{item.count}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}